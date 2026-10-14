<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Add stock to a specific warehouse (Purchase, Opening Balance, or Return).
     * Creates an inventory batch for FIFO tracking and a stock movement record.
     *
     * @return array{batch: InventoryBatch, movement: StockMovement, balance: StockBalance}
     */
    public function addStock(
        int $productId,
        int $warehouseId,
        float $quantity,
        ?float $costPrice = null,
        ?string $notes = null,
        string $referenceType = 'manual_in',
        ?int $referenceId = null,
        ?int $userId = null,
        ?Carbon $receivedAt = null,
        ?int $supplierId = null
    ): array {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('الكمية المضافة يجب أن تكون أكبر من الصفر.');
        }

        return DB::transaction(function () use (
            $productId,
            $warehouseId,
            $quantity,
            $costPrice,
            $notes,
            $referenceType,
            $referenceId,
            $userId,
            $receivedAt,
            $supplierId
        ) {
            $product = Product::findOrFail($productId);
            $warehouse = Warehouse::findOrFail($warehouseId);

            $effectiveCost = $costPrice !== null ? $costPrice : (float) $product->cost_price;

            // 1. Create FIFO Inventory Batch
            $batch = InventoryBatch::create([
                'batch_number' => InventoryBatch::generateBatchNumber(),
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'purchase_invoice_id' => $referenceType === 'purchase_invoice' ? $referenceId : null,
                'initial_quantity' => $quantity,
                'remaining_quantity' => $quantity,
                'cost_price' => $effectiveCost,
                'received_at' => $receivedAt ?? now(),
                'is_exhausted' => false,
                'notes' => $notes,
            ]);

            // 2. Update Warehouse Stock Balance
            $balance = StockBalance::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => 0.00, 'reserved_quantity' => 0.00]
            );

            $balance->increment('quantity', $quantity);

            $runningBalance = (float) StockBalance::where('product_id', $product->id)->sum('quantity');

            // 3. Create Audit Stock Movement
            $movement = StockMovement::create([
                'movement_number' => StockMovement::generateMovementNumber(),
                'type' => StockMovement::TYPE_IN,
                'product_id' => $product->id,
                'from_warehouse_id' => null,
                'to_warehouse_id' => $warehouse->id,
                'batch_id' => $batch->id,
                'quantity' => $quantity,
                'running_balance' => $runningBalance,
                'unit_cost' => $effectiveCost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'user_id' => $userId,
                'supplier_id' => $supplierId,
                'notes' => $notes,
            ]);

            return [
                'batch' => $batch,
                'movement' => $movement,
                'balance' => $balance->fresh(),
            ];
        });
    }

    /**
     * Deduct stock using FIFO (First-In, First-Out) logic.
     * Consumes older batches first and records granular movement lines.
     *
     * @return array{movements: list<StockMovement>, total_deducted: float, total_cogs: float}
     */
    public function deductStockFIFO(
        int $productId,
        int $warehouseId,
        float $quantity,
        ?string $notes = null,
        string $referenceType = 'manual_out',
        ?int $referenceId = null,
        ?int $userId = null,
        ?int $customerId = null
    ): array {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('الكمية المطلوب صرفها يجب أن تكون أكبر من الصفر.');
        }

        return DB::transaction(function () use (
            $productId,
            $warehouseId,
            $quantity,
            $notes,
            $referenceType,
            $referenceId,
            $userId,
            $customerId
        ) {
            $product = Product::findOrFail($productId);
            $warehouse = Warehouse::findOrFail($warehouseId);

            $balance = StockBalance::where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->lockForUpdate()
                ->first();

            $currentStock = $balance ? (float) $balance->quantity : 0.00;

            if ($currentStock < $quantity) {
                throw new InvalidArgumentException(
                    "الرصيد المتاح للصنف ({$product->name}) في مستودع ({$warehouse->name}) هو {$currentStock} ولا يكفي لصرف {$quantity}."
                );
            }

            // Retrieve available batches in strict FIFO order
            $batches = InventoryBatch::where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->availableFIFO()
                ->lockForUpdate()
                ->get();

            $remainingToDeduct = $quantity;
            $movements = [];
            $totalCogs = 0.00;

            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

                $batchAvailable = (float) $batch->remaining_quantity;
                $deductFromThisBatch = min($remainingToDeduct, $batchAvailable);

                $batch->remaining_quantity = $batchAvailable - $deductFromThisBatch;
                if ($batch->remaining_quantity <= 0) {
                    $batch->is_exhausted = true;
                }
                $batch->save();

                $batchCogs = $deductFromThisBatch * (float) $batch->cost_price;
                $totalCogs += $batchCogs;

                $balance->decrement('quantity', $deductFromThisBatch);
                $runningBalance = (float) StockBalance::where('product_id', $product->id)->sum('quantity');

                // Create movement per consumed batch chunk for perfect auditability
                $movement = StockMovement::create([
                    'movement_number' => StockMovement::generateMovementNumber(),
                    'type' => StockMovement::TYPE_OUT,
                    'product_id' => $product->id,
                    'from_warehouse_id' => $warehouse->id,
                    'to_warehouse_id' => null,
                    'batch_id' => $batch->id,
                    'quantity' => $deductFromThisBatch,
                    'running_balance' => $runningBalance,
                    'unit_cost' => (float) $batch->cost_price,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'user_id' => $userId,
                    'customer_id' => $customerId,
                    'notes' => $notes,
                ]);

                $movements[] = $movement;
                $remainingToDeduct -= $deductFromThisBatch;
            }

            // If there were unbatched historical stocks, handle remaining
            if ($remainingToDeduct > 0) {
                $balance->decrement('quantity', $remainingToDeduct);
                $runningBalance = (float) StockBalance::where('product_id', $product->id)->sum('quantity');

                $movement = StockMovement::create([
                    'movement_number' => StockMovement::generateMovementNumber(),
                    'type' => StockMovement::TYPE_OUT,
                    'product_id' => $product->id,
                    'from_warehouse_id' => $warehouse->id,
                    'to_warehouse_id' => null,
                    'batch_id' => null,
                    'quantity' => $remainingToDeduct,
                    'running_balance' => $runningBalance,
                    'unit_cost' => (float) $product->cost_price,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'user_id' => $userId,
                    'customer_id' => $customerId,
                    'notes' => $notes,
                ]);
                $movements[] = $movement;
                $totalCogs += $remainingToDeduct * (float) $product->cost_price;
            }

            return [
                'movements' => $movements,
                'total_deducted' => $quantity,
                'total_cogs' => $totalCogs,
            ];
        });
    }

    /**
     * Transfer stock between warehouses atomically.
     */
    public function transferStock(
        int $productId,
        int $fromWarehouseId,
        int $toWarehouseId,
        float $quantity,
        ?string $notes = null,
        ?int $userId = null
    ): array {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new InvalidArgumentException('المستودع المحول منه والمحول إليه يجب أن يكونا مختلفين.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('كمية التحويل يجب أن تكون أكبر من الصفر.');
        }

        return DB::transaction(function () use (
            $productId,
            $fromWarehouseId,
            $toWarehouseId,
            $quantity,
            $notes,
            $userId
        ) {
            $deduction = $this->deductStockFIFO(
                $productId,
                $fromWarehouseId,
                $quantity,
                'تحويل صادر: '.$notes,
                'stock_transfer',
                null,
                $userId
            );

            // Add stock to destination warehouse
            $addition = $this->addStock(
                $productId,
                $toWarehouseId,
                $quantity,
                $deduction['total_cogs'] / $quantity,
                'تحويل وارد: '.$notes,
                'stock_transfer',
                null,
                $userId
            );

            // Create transfer movement linking both warehouses
            $transferMovement = StockMovement::create([
                'movement_number' => StockMovement::generateMovementNumber(),
                'type' => StockMovement::TYPE_TRANSFER,
                'product_id' => $productId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'batch_id' => $addition['batch']->id,
                'quantity' => $quantity,
                'running_balance' => (float) StockBalance::where('product_id', $productId)->sum('quantity'),
                'unit_cost' => $deduction['total_cogs'] / $quantity,
                'reference_type' => 'stock_transfer',
                'reference_id' => null,
                'user_id' => $userId,
                'notes' => $notes,
            ]);

            return [
                'transfer_movement' => $transferMovement,
                'deduction' => $deduction,
                'addition' => $addition,
            ];
        });
    }

    /**
     * Perform inventory count adjustment (تسوية جردية).
     */
    public function adjustStock(
        int $productId,
        int $warehouseId,
        float $actualQuantity,
        string $reason,
        ?int $userId = null
    ): array {
        if ($actualQuantity < 0) {
            throw new InvalidArgumentException('الرصيد الفعلي لا يمكن أن يكون سالباً.');
        }

        return DB::transaction(function () use ($productId, $warehouseId, $actualQuantity, $reason, $userId) {
            $balance = StockBalance::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['quantity' => 0.00, 'reserved_quantity' => 0.00]
            );

            $currentQuantity = (float) $balance->quantity;
            $diff = $actualQuantity - $currentQuantity;

            if ($diff == 0) {
                return ['status' => 'unchanged', 'balance' => $balance];
            }

            if ($diff > 0) {
                // Shortage correction / surplus: Add stock
                $result = $this->addStock(
                    $productId,
                    $warehouseId,
                    $diff,
                    null,
                    'تسوية جردية بالزيادة: '.$reason,
                    'inventory_adjustment',
                    null,
                    $userId
                );

                return ['status' => 'increased', 'diff' => $diff, 'result' => $result];
            }

            // Deficit: Deduct stock via FIFO
            $result = $this->deductStockFIFO(
                $productId,
                $warehouseId,
                abs($diff),
                'تسوية جردية بالعجز: '.$reason,
                'inventory_adjustment',
                null,
                $userId
            );

            return ['status' => 'decreased', 'diff' => $diff, 'result' => $result];
        });
    }

    /**
     * Get aggregate inventory metrics.
     *
     * @return array<string, mixed>
     */
    public function getInventoryMetrics(): array
    {
        $totalProducts = Product::count();
        $totalStockUnits = (float) StockBalance::sum('quantity');

        // Total valuation based on FIFO remaining batches
        $batchValuation = (float) InventoryBatch::where('is_exhausted', false)
            ->sum(DB::raw('remaining_quantity * cost_price'));

        // If some items don't have batches yet, calculate from products and stock_balances
        if ($batchValuation <= 0 && $totalStockUnits > 0) {
            $batchValuation = (float) DB::table('stock_balances')
                ->join('products', 'stock_balances.product_id', '=', 'products.id')
                ->sum(DB::raw('stock_balances.quantity * products.cost_price'));
        }

        $lowStockCount = Product::whereHas('stockBalances')
            ->withSum('stockBalances', 'quantity')
            ->get()
            ->filter(fn (Product $p) => ((float) $p->stock_balances_sum_quantity) <= (float) $p->min_stock_alert && ((float) $p->stock_balances_sum_quantity) > 0)
            ->count();

        $outOfStockCount = Product::withSum('stockBalances', 'quantity')
            ->get()
            ->filter(fn (Product $p) => ((float) $p->stock_balances_sum_quantity) <= 0)
            ->count();

        return [
            'total_products' => $totalProducts,
            'total_stock_units' => $totalStockUnits,
            'total_inventory_value' => $batchValuation,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
        ];
    }
}

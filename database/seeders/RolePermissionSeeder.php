<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Products
            ['name' => 'عرض المنتجات والأصناف', 'slug' => 'view-products', 'module' => 'المنتجات', 'description' => 'إمكانية تصفح قائمة الأصناف والبحث بالباركود والتفاصيل'],
            ['name' => 'إدارة المنتجات والتصنيفات', 'slug' => 'manage-products', 'module' => 'المنتجات', 'description' => 'إضافة أصناف جديدة، تعديل المواصفات العامة، وإدارة تصنيفات الأصناف'],
            ['name' => 'تحديد وتعديل أسعار المنتجات والتكلفة', 'slug' => 'edit-product-prices', 'module' => 'المنتجات', 'description' => 'تعديل أسعار التجزئة والجملة والموزع وسعر التكلفة وتكلفة أول المدة'],

            // Inventory
            ['name' => 'عرض المخزون والمستودعات', 'slug' => 'access-inventory', 'module' => 'المخازن والمستودعات', 'description' => 'الاطلاع على كميات المخزون، النواقص، وحركات المستودعات'],
            ['name' => 'إجراء حركات التحويل والتسوية', 'slug' => 'manage-inventory', 'module' => 'المخازن والمستودعات', 'description' => 'إنشاء حركات تحويل بين المخازن، تسويات الجرد، وأرصدة الدفعات'],

            // Purchases
            ['name' => 'الوصول لفواتير الشراء والتوريد', 'slug' => 'access-purchases', 'module' => 'المشتريات والواردات', 'description' => 'استعراض فواتير الشراء وإنشاء مسودات التوريد'],
            ['name' => 'إدارة الموردين ومكاتب الشحن', 'slug' => 'manage-purchases', 'module' => 'المشتريات والواردات', 'description' => 'إضافة وتعديل بيانات الموردين، حسابات الشحن، وتسجيل الفواتير'],
            ['name' => 'اعتماد وترحيل فواتير الشراء', 'slug' => 'post-purchase-invoice', 'module' => 'المشتريات والواردات', 'description' => 'ترحيل الفاتورة للتأثير المالي وتوليد دفعات FIFO المخزنية'],
            ['name' => 'إلغاء فواتير الشراء المرحلة', 'slug' => 'cancel-purchase-invoice', 'module' => 'المشتريات والواردات', 'description' => 'إلغاء الفواتير وإرجاع التأثير المالي والكميات'],

            // Dashboard & Analytics
            ['name' => 'الوصول للوحة التحكم والتقارير العامة', 'slug' => 'access-dashboard', 'module' => 'الإدارة والنظام', 'description' => 'الاطلاع على لوحة المؤشرات والإحصائيات والرسوم البيانية العامة للمؤسسة'],

            // Sales & Cashier
            ['name' => 'الوصول لنقطة البيع وفواتير المبيعات', 'slug' => 'access-sales', 'module' => 'المبيعات ونقاط البيع', 'description' => 'فتح شاشة الكاشير (POS) واستعراض فواتير البيع'],
            ['name' => 'إدارة المبيعات والخصومات', 'slug' => 'manage-sales', 'module' => 'المبيعات ونقاط البيع', 'description' => 'تعديل بنود البيع والخصومات وفئات الأسعار للعملاء'],
            ['name' => 'ترحيل واعتماد فواتير البيع', 'slug' => 'post-sales-invoice', 'module' => 'المبيعات ونقاط البيع', 'description' => 'صرف البضاعة بالـ FIFO وتحصيل المبالغ وتأثير الخزينة'],
            ['name' => 'إلغاء فواتير البيع وارتجاع البضاعة', 'slug' => 'void-sales-invoice', 'module' => 'المبيعات ونقاط البيع', 'description' => 'إلغاء فواتير البيع المرحلة واسترجاع التكلفة والكميات'],
            ['name' => 'الاطلاع على تكلفة البضاعة وهوامش الربح', 'slug' => 'view-cogs', 'module' => 'المبيعات ونقاط البيع', 'description' => 'كشف أرقام تكلفة المبيعات ومجمل أرباح الفواتير'],

            // Cashier Shifts & Cash Drawer
            ['name' => 'فتح وردية كاشير جديدة لنفسه', 'slug' => 'open-shifts', 'module' => 'المبيعات ونقاط البيع', 'description' => 'إمكانية فتح وردية كاشير وإدخال رصيد بداية العهدة بالدرج للبدء بالبيع'],
            ['name' => 'تسجيل حركات نقدية بالدرج (سحب/إيداع)', 'slug' => 'manage-shifts', 'module' => 'المبيعات ونقاط البيع', 'description' => 'تسجيل السحب النقدي للمصروفات النثرية أو الإيداع الإضافي بالدرج'],
            ['name' => 'إغلاق ورديات الكاشير واعتماد جرد الدرج', 'slug' => 'close-shifts', 'module' => 'المبيعات ونقاط البيع', 'description' => 'صلاحية إدارية حصرية لإغلاق وردية الكاشير واعتماد الجرد الفعلي للنقدية وحساب الفارق'],

            // Customers & Sales
            ['name' => 'إضافة عميل سريع من شاشة الكاشير (POS)', 'slug' => 'create-customers-pos', 'module' => 'المبيعات ونقاط البيع', 'description' => 'إمكانية إضافة عميل جديد مباشرة من شاشة البيع وتحديد فئته السعرية'],
            ['name' => 'إدارة العملاء وتجار الجملة', 'slug' => 'manage-customers', 'module' => 'العملاء والمبيعات', 'description' => 'إضافة وتعديل العملاء وكشوف الحساب والمدفوعات'],

            // System Administration
            ['name' => 'إدارة إعدادات الشركة والفروع', 'slug' => 'manage-company-settings', 'module' => 'الإدارة والنظام', 'description' => 'تعديل البيانات التجارية للشركة، الشعار، وإعدادات النظام العامة'],
            ['name' => 'تصميم وإدارة قوالب الطباعة', 'slug' => 'manage-print-templates', 'module' => 'الإدارة والنظام', 'description' => 'تخصيص قوالب طباعة الفواتير والملصقات الحرارية'],
            ['name' => 'إدارة المستخدمين وصلاحيات الدخول', 'slug' => 'manage-users', 'module' => 'الإدارة والنظام', 'description' => 'إضافة المستخدمين وتعيين كلمات المرور وتحديد أدوارهم'],
            ['name' => 'إدارة الأدوار ومصفوفة الصلاحيات', 'slug' => 'manage-roles', 'module' => 'الإدارة والنظام', 'description' => 'إنشاء وتعديل الأدوار الديناميكية وتخصيص صلاحيات كل دور'],
            ['name' => 'إدارة وتخصيص وحدات القياس', 'slug' => 'manage-units', 'module' => 'الإدارة والنظام', 'description' => 'إنشاء وحدات القياس وضبط معاملات التحويل وكميات التعبئة'],

            // Sales Representatives & Field Agents
            ['name' => 'الوصول لبوابة المندوب والمبيعات الميدانية', 'slug' => 'access-sales-portal', 'module' => 'مندوبي المبيعات', 'description' => 'تصفح كتالوج المنتجات وتسجيل طلبات وفواتير العملاء من الموبايل'],
            ['name' => 'الاطلاع على العمولات الشخصية', 'slug' => 'view-own-commissions', 'module' => 'مندوبي المبيعات', 'description' => 'استعراض تقرير العمولات والأرباح المحققة الخاصة بالمندوب'],
            ['name' => 'إدارة واعتماد عمولات المناديب', 'slug' => 'manage-commissions', 'module' => 'مندوبي المبيعات', 'description' => 'تحديد نسب العمولات واعتمادها وصرفها للمناديب'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        $allPermissionIds = Permission::pluck('id')->toArray();

        // 1. Admin Role
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'مدير النظام',
                'description' => 'يمتلك كافة الصلاحيات الإدارية والمالية والمخزنية في النظام',
                'is_system' => true,
            ]
        );
        $adminRole->permissions()->sync($allPermissionIds);

        // 2. Warehouse Role
        $warehouseRole = Role::firstOrCreate(
            ['slug' => 'warehouse'],
            [
                'name' => 'أمين المستودع',
                'description' => 'إدارة المنتجات، المخازن، حركات الجرد، وفواتير التوريد بدون صلاحيات تعديل الأسعار',
                'is_system' => true,
            ]
        );
        $warehousePerms = Permission::whereIn('slug', [
            'view-products',
            'manage-products',
            'access-inventory',
            'manage-inventory',
            'access-purchases',
            'manage-purchases',
            'access-sales',
        ])->pluck('id')->toArray();
        $warehouseRole->permissions()->sync($warehousePerms);

        // 3. Cashier Role (Cashier CAN open shifts, but CANNOT close without manager approval)
        $cashierRole = Role::firstOrCreate(
            ['slug' => 'cashier'],
            [
                'name' => 'كاشير نقطة البيع',
                'description' => 'عرض واستعلام المنتجات والباركود والأسعار لإصدار أوامر البيع',
                'is_system' => true,
            ]
        );
        $cashierPerms = Permission::whereIn('slug', [
            'view-products',
            'access-sales',
            'manage-sales',
            'post-sales-invoice',
            'open-shifts',
            'manage-shifts',
            'create-customers-pos',
        ])->pluck('id')->toArray();
        $cashierRole->permissions()->sync($cashierPerms);

        // 4. Sales Representative Role (Field Agent / Delegate)
        $salesRepRole = Role::firstOrCreate(
            ['slug' => 'sales_rep'],
            [
                'name' => 'مندوب مبيعات خارجي / بياع',
                'description' => 'الوصول لبوابة المندوب من الموبايل لتسجيل العملاء وتصفح المنتجات وإصدار الطلبات ومتابعة العمولات',
                'is_system' => true,
            ]
        );
        $salesRepPerms = Permission::whereIn('slug', [
            'view-products',
            'create-customers-pos',
            'access-sales-portal',
            'view-own-commissions',
        ])->pluck('id')->toArray();
        $salesRepRole->permissions()->sync($salesRepPerms);

        // Sync all permissions to any company manager role
        $companyManagerRoles = Role::where('slug', 'مدير_للشركه')
            ->orWhere('name', 'like', '%مدير%')
            ->get();
        foreach ($companyManagerRoles as $cmr) {
            $cmr->permissions()->sync($allPermissionIds);
        }

        // Assign existing admin user to admin role
        $adminUser = User::where('role', User::ROLE_ADMIN)->first();
        if ($adminUser && ! $adminUser->role_id) {
            $adminUser->update(['role_id' => $adminRole->id]);
        }
    }
}

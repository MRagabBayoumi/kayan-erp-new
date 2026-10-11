<?php

test('the application returns a successful response with Kayan ERP layout', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Kayan ERP');
    $response->assertSee('dir="rtl"', false);
    $response->assertSee('data-theme="light"', false);
    $response->assertSee('data-density="comfortable"', false);
    $response->assertSee('appLauncherBackdrop');
    $response->assertSee('erpSidebar');
    $response->assertSee('sidebarToggleBtn');
    $response->assertSee('sidebarDesktopToggle');
    $response->assertSee('لوحة التحكم');
    $response->assertSee('Cairo');
});

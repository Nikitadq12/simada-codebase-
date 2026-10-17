<?php
/** Class DashboardController - ringkasan progres pengadaan (UC-11, FR-08). */
class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireRole();
        $paket = (new PaketPengadaan())->allWithProgress();
        $count = fn($s) => count(array_filter($paket, fn($p) => $p['status'] === $s));
        $this->render('dashboard/index', [
            'aktif'      => count($paket) - $count('Selesai'),
            'tender'     => $count('Berjalan'),
            'selesai'    => $count('Selesai'),
            'totalUser'  => (new User())->countAll(),
            'terbaru'    => array_slice($paket, 0, 5),
        ], 'dashboard');
    }
}

<?php
/** Class ProgresController - Pantau Progres Pengadaan (UC-07, FR-06). Hanya-baca. */
class ProgresController extends Controller
{
    public function index(): void
    {
        Auth::requireRole();
        $list = (new PaketPengadaan())->allWithProgress();
        $sel = null; $stages = [];
        if ($id = (int)($_GET['id'] ?? 0)) {
            foreach ($list as $p) if ((int)$p['id'] === $id) $sel = $p;
            if ($sel) $stages = TenderController::stages((new Tender())->byPaket($id));
        }
        $this->render('progres/index', compact('list', 'sel', 'stages'), 'progres');
    }
}

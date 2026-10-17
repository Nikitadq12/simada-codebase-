<?php
/**
 * Class TenderController - Modul 3: Kelola e-Tendering (UC-03, FR-02).
 * Pokja (dan Admin) dapat membuat paket dan mengelola tahapan tender;
 * PA/KPA dan PPK hanya dapat melihat.
 */
class TenderController extends Controller
{
    private PaketPengadaan $paket;
    private Tender $tender;

    public function __construct()
    {
        Auth::requireRole();
        $this->paket = new PaketPengadaan();
        $this->tender = new Tender();
    }

    private function canEdit(): bool
    {
        return in_array(Auth::user()['role'], ['Pokja', 'Admin'], true);
    }

    /** Daftar paket + modal "Paket Baru". */
    public function index(): void
    {
        $modal = isset($_GET['form']); $data = ['nama_paket' => '', 'metode' => 'Tender']; $errors = [];
        if ($this->isPost()) {
            if (!$this->canEdit()) $this->forbidden();
            $modal = true;
            $data = ['nama_paket' => trim($_POST['nama_paket'] ?? ''), 'metode' => $_POST['metode'] ?? ''];
            $v = (new Validator())->required('nama_paket', $data['nama_paket'], 'Nama paket')
                                  ->required('metode', $data['metode'], 'Metode');
            if ($data['metode'] && !in_array($data['metode'], PaketPengadaan::METODE, true)) {
                $v->addError('metode', 'Metode tidak valid.');
            }
            if (!$v->fails()) {
                $id = $this->paket->create($data, Auth::user()['id']);
                $this->redirect("page=paket&id=$id&tab=tender", 'Paket pengadaan berhasil dibuat.');
            }
            $errors = $v->errors();
        }
        $this->render('tender/index', [
            'list' => $this->paket->allWithProgress(), 'canEdit' => $this->canEdit(),
            'modal' => $modal && $this->canEdit(), 'data' => $data, 'errors' => $errors,
        ], 'tender');
    }

    /** Detail Paket Pengadaan dengan tab Info Umum | e-Tendering | Progres. */
    public function detail(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $paket = $this->paket->findById($id);
        if (!$paket) $this->redirect('page=tender', 'Paket tidak ditemukan.');
        $tab = in_array($_GET['tab'] ?? '', ['info', 'tender', 'progres'], true) ? $_GET['tab'] : 'tender';
        $rows = $this->tender->byPaket($id);
        $errors = [];

        if ($this->isPost()) {
            if (!$this->canEdit()) $this->forbidden();
            $rows = array_values(array_map(fn($r) => [
                'id'              => (int)($r['id'] ?? 0),
                'tahap'           => trim($r['tahap'] ?? ''),
                'tanggal_mulai'   => trim($r['tanggal_mulai'] ?? ''),
                'tanggal_selesai' => trim($r['tanggal_selesai'] ?? ''),
                'status'          => $r['status'] ?? 'Belum dimulai',
            ], $_POST['rows'] ?? []));
            if (($_POST['action'] ?? '') === 'add') {
                // "+ Tambah Tahap": tambah baris kosong tanpa menyimpan, isian lain tetap dipertahankan
                $rows[] = ['id' => 0, 'tahap' => '', 'tanggal_mulai' => '', 'tanggal_selesai' => '', 'status' => 'Belum dimulai'];
                $errors = [];
            } elseif (!($errors = Tender::validateRows($rows))) {
                $this->tender->saveRows($id, $rows);
                $this->paket->updateStatus($id, Tender::deriveStatusPaket($this->tender->byPaket($id)));
                $this->redirect("page=paket&id=$id&tab=tender", 'Tahapan tender berhasil disimpan.');
            }
            $tab = 'tender';
        }

        $this->render('tender/detail', [
            'paket' => $paket, 'rows' => $rows, 'errors' => $errors, 'tab' => $tab,
            'canEdit' => $this->canEdit(), 'pembuat' => $this->paket->creatorName((int)$paket['created_by']),
            'stages' => $this->stages($this->tender->byPaket($id)),
        ], 'tender');
    }

    /** Peta 6 tahap baku -> status, dipakai stepper Progres. */
    public static function stages(array $tahapan): array
    {
        $by = array_column($tahapan, 'status', 'tahap');
        return array_map(fn($t) => ['tahap' => $t, 'status' => $by[$t] ?? 'Belum dimulai'], Tender::TAHAP);
    }
}

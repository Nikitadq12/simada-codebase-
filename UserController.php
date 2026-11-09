<?php
/**
 * Class UserController - Modul 2: Kelola Pengguna & Role (UC-02, FR-01).
 * Hanya dapat diakses role Admin. Form Tambah/Edit tampil sebagai modal.
 */
class UserController extends Controller
{
    private User $users;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $this->users = new User();
    }

    public function index(): void
    {
        $id = (int)($_GET['edit'] ?? 0);
        $modal = isset($_GET['form']) || $id;
        $data = ['nama' => '', 'email' => '', 'role_id' => '', 'password' => ''];
        if ($id) {
            $data = $this->users->findById($id) ?? $this->redirect('page=users', 'Pengguna tidak ditemukan.');
        }
        $errors = [];

        if ($this->isPost()) {
            $modal = true;
            $data = [
                'nama'     => trim($_POST['nama'] ?? ''),
                'email'    => trim($_POST['email'] ?? ''),
                'role_id'  => (int)($_POST['role_id'] ?? 0),
                'password' => $_POST['password'] ?? '',
            ];
            $errors = $this->validate($data, $id);
            if (!$errors) {
                $id ? $this->users->update($id, $data) : $this->users->create($data);
                $this->redirect('page=users', $id ? 'Data pengguna berhasil diperbarui.' : 'Pengguna baru berhasil ditambahkan.');
            }
        }

        $this->render('users/index', [
            'list' => $this->users->allWithRole(), 'roles' => (new Role())->findAll(),
            'modal' => $modal, 'id' => $id, 'data' => $data, 'errors' => $errors,
        ], 'users');
    }

    private function validate(array $d, int $id): array
    {
        $v = (new Validator())
            ->required('nama', $d['nama'], 'Nama')
            ->required('email', $d['email'], 'Email')->email('email', $d['email'])
            ->required('role_id', $d['role_id'] ?: '', 'Role');
        if (!$id) {
            $v->required('password', $d['password'], 'Password')->minLength('password', $d['password'], 8, 'Password');
        }
        if (!isset($v->errors()['email']) && $this->users->emailExists($d['email'], $id ?: null)) {
            $v->addError('email', 'Email sudah digunakan pengguna lain.');
        }
        if ($d['role_id'] && !(new Role())->findById($d['role_id'])) {
            $v->addError('role_id', 'Role tidak valid.');
        }
        return $v->errors();
    }

    public function toggle(): void
    {
        if (!$this->isPost()) $this->redirect('page=users');
        $id = (int)($_POST['id'] ?? 0);
        $u = $this->users->findById($id);
        if (!$u) $this->redirect('page=users', 'Pengguna tidak ditemukan.');
        if ($id === Auth::user()['id']) $this->redirect('page=users', 'Anda tidak dapat menonaktifkan akun sendiri.');
        $new = $u['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        $this->users->setStatus($id, $new);
        $this->redirect('page=users', "Status {$u['nama']} diubah menjadi " . ucfirst($new) . '.');
    }
}

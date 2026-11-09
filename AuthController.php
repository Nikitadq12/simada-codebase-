<?php
/** Class AuthController - Modul 1: Login & Logout (UC-01, FR-26). */
class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) $this->redirect('page=dashboard');
        $error = null; $email = '';
        if ($this->isPost()) {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $v = (new Validator())
                ->required('email', $email, 'Email')->email('email', $email)
                ->required('password', $password, 'Password');
            if ($v->fails()) {
                $error = implode(' ', $v->errors());
            } elseif (($error = Auth::attempt($email, $password)) === null) {
                $this->redirect('page=dashboard');
            }
        }
        $this->render('auth/login', compact('error', 'email'));
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: index.php?page=login');
        exit;
    }
}

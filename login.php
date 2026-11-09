<div class="login-wrap">
<div class="login-card">
  <div class="logo">Logo</div>
  <h1 class="title">SIMADA</h1>
  <p class="muted">Sistem Informasi Manajemen Pengadaan</p>
  <form method="post" action="index.php?page=login" novalidate>
    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
    <label>Email<input type="email" name="email" placeholder="email@contoh.go.id" value="<?= $e($email) ?>"></label>
    <label>Password<input type="password" name="password" placeholder="••••••••"></label>
    <?php if ($error): ?><p class="login-error"><?= $e($error) ?></p><?php endif; ?>
    <button class="btn block" type="submit">Masuk</button>
  </form>
</div>
<p class="foot">Bagian Pengadaan Barang/Jasa Setda Kab. Kebumen</p>
</div>

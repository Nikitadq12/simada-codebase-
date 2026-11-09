<div class="head"><h1>Manajemen Pengguna</h1><a class="btn" href="index.php?page=users&form=new">+ Tambah User</a></div>
<table><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($list as $u): ?>
<tr><td><?= $e($u['nama']) ?></td><td><?= $e($u['email']) ?></td><td><?= $e($u['nama_role']) ?></td>
<td class="st-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></td>
<td class="aksi"><a href="index.php?page=users&edit=<?= $u['id'] ?>">Edit</a> |
<form method="post" action="index.php?page=user-toggle"><input type="hidden" name="_csrf" value="<?= $csrf ?>"><input type="hidden" name="id" value="<?= $u['id'] ?>">
<button class="link"><?= $u['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?></button></form></td></tr>
<?php endforeach; ?></tbody></table>
<div class="note">Klik Tambah User / Edit: muncul form (Nama, Email, Role, Simpan)</div>

<?php if ($modal): ?>
<div class="overlay"><form method="post" action="index.php?page=users<?= $id ? "&edit=$id" : '' ?>" class="modal" novalidate>
  <input type="hidden" name="_csrf" value="<?= $csrf ?>">
  <h2><?= $id ? 'Edit Pengguna' : 'Tambah Pengguna' ?></h2>
  <label>Nama<input name="nama" value="<?= $e($data['nama']) ?>"><?php if (isset($errors['nama'])): ?><small class="err"><?= $e($errors['nama']) ?></small><?php endif; ?></label>
  <label>Email<input type="email" name="email" value="<?= $e($data['email']) ?>"><?php if (isset($errors['email'])): ?><small class="err"><?= $e($errors['email']) ?></small><?php endif; ?></label>
  <label>Role<select name="role_id"><option value="">-- pilih role --</option>
  <?php foreach ($roles as $r): ?><option value="<?= $r['id'] ?>" <?= (int)$data['role_id'] === (int)$r['id'] ? 'selected' : '' ?>><?= $e($r['nama_role']) ?></option><?php endforeach; ?>
  </select><?php if (isset($errors['role_id'])): ?><small class="err"><?= $e($errors['role_id']) ?></small><?php endif; ?></label>
  <?php if (!$id): ?><label>Password<input type="password" name="password"><?php if (isset($errors['password'])): ?><small class="err"><?= $e($errors['password']) ?></small><?php endif; ?></label><?php endif; ?>
  <div class="row end"><a class="btn ghost" href="index.php?page=users">Batal</a><button class="btn" type="submit">Simpan</button></div>
</form></div>
<?php endif; ?>

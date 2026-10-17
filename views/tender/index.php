<div class="head"><h1>e-Tendering</h1><?php if ($canEdit): ?><a class="btn" href="index.php?page=tender&form=new">+ Paket Baru</a><?php endif; ?></div>
<p class="hint">Pilih paket untuk mengelola tahapan tendernya</p>
<table><thead><tr><th>Nama Paket</th><th>Metode</th><th>Tanggal Dibuat</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php if (!$list): ?><tr><td colspan="5" class="muted">Belum ada data</td></tr><?php endif; ?>
<?php foreach ($list as $p): ?>
<tr><td><?= $e($p['nama_paket']) ?></td><td><?= $e($p['metode']) ?></td><td><?= $e($p['tanggal']) ?></td>
<td class="st-<?= strtolower($p['status']) ?>"><?= $e($p['status']) ?></td><td><a href="index.php?page=paket&id=<?= $p['id'] ?>">Detail</a></td></tr>
<?php endforeach; ?></tbody></table>

<?php if ($modal): ?>
<div class="overlay"><form method="post" action="index.php?page=tender" class="modal" novalidate>
  <input type="hidden" name="_csrf" value="<?= $csrf ?>">
  <h2>Buat Paket Pengadaan</h2>
  <label>Nama Paket<input name="nama_paket" value="<?= $e($data['nama_paket']) ?>"><?php if (isset($errors['nama_paket'])): ?><small class="err"><?= $e($errors['nama_paket']) ?></small><?php endif; ?></label>
  <label>Metode<select name="metode"><option value="">-- pilih metode --</option>
  <?php foreach (PaketPengadaan::METODE as $m): ?><option <?= $data['metode'] === $m ? 'selected' : '' ?>><?= $m ?></option><?php endforeach; ?>
  </select><?php if (isset($errors['metode'])): ?><small class="err"><?= $e($errors['metode']) ?></small><?php endif; ?></label>
  <div class="row end"><a class="btn ghost" href="index.php?page=tender">Batal</a><button class="btn" type="submit">Simpan</button></div>
</form></div>
<?php endif; ?>

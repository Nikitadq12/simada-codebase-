<h1>Detail Paket Pengadaan</h1>
<div class="cards info">
  <div class="card c-blue"><span>Nama Paket</span><?= $e($paket['nama_paket']) ?></div>
  <div class="card c-green"><span>Metode</span><?= $e($paket['metode']) ?></div>
  <div class="card c-orange"><span>Status</span><?= $e($paket['status']) ?></div>
  <div class="card c-red"><span>Tanggal Dibuat</span><?= $e($paket['tanggal']) ?></div>
</div>
<nav class="tabs">
<?php foreach (['info' => 'Info Umum', 'tender' => 'e-Tendering', 'progres' => 'Progres'] as $k => $label): ?>
  <a class="<?= $tab === $k ? 'on' : '' ?>" href="index.php?page=paket&id=<?= $paket['id'] ?>&tab=<?= $k ?>"><?= $label ?></a>
<?php endforeach; ?>
</nav>
<section class="tabbody">
<?php if ($tab === 'info'): ?>
  <table class="kv"><tr><th>Nama Paket</th><td><?= $e($paket['nama_paket']) ?></td></tr><tr><th>Metode</th><td><?= $e($paket['metode']) ?></td></tr>
  <tr><th>Status</th><td><?= $e($paket['status']) ?></td></tr><tr><th>Tanggal Dibuat</th><td><?= $e($paket['tanggal']) ?></td></tr><tr><th>Dibuat oleh</th><td><?= $e($pembuat) ?></td></tr></table>
<?php elseif ($tab === 'progres'): ?>
  <?php require __DIR__ . '/../progres/stepper.php'; ?>
<?php else: ?>
  <?php if ($errors): ?><div class="alert err"><?php foreach ($errors as $m): ?><div><?= $e($m) ?></div><?php endforeach; ?></div><?php endif; ?>
  <form method="post" action="index.php?page=paket&id=<?= $paket['id'] ?>&tab=tender" novalidate>
  <input type="hidden" name="_csrf" value="<?= $csrf ?>">
  <table class="grid"><thead><tr><th>Tahap</th><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Status</th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="4" class="muted">Belum ada tahapan tender. Klik "+ Tambah Tahap".</td></tr><?php endif; ?>
  <?php foreach ($rows as $i => $r): $n = "rows[$i]"; $st = strtolower(str_replace(' ', '-', $r['status'])); ?>
  <tr>
  <?php if (!$canEdit): ?>
    <td><?= $e($r['tahap']) ?></td><td><?= $e($r['tanggal_mulai'] ?: '-') ?></td><td><?= $e($r['tanggal_selesai'] ?: '-') ?></td><td class="st-<?= $st ?>"><?= $e($r['status']) ?></td>
  <?php else: ?>
    <td><input type="hidden" name="<?= $n ?>[id]" value="<?= (int)$r['id'] ?>">
      <?php if ($r['id']): ?><input type="hidden" name="<?= $n ?>[tahap]" value="<?= $e($r['tahap']) ?>"><?= $e($r['tahap']) ?>
      <?php else: ?><select name="<?= $n ?>[tahap]"><option value="">-- pilih tahap --</option>
        <?php foreach (Tender::TAHAP as $t): if (!in_array($t, array_column(array_filter($rows, fn($x) => $x['id']), 'tahap'), true)): ?><option <?= $r['tahap'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endif; endforeach; ?></select>
      <?php endif; ?></td>
    <td><input type="date" name="<?= $n ?>[tanggal_mulai]" value="<?= $e($r['tanggal_mulai']) ?>"></td>
    <td><input type="date" name="<?= $n ?>[tanggal_selesai]" value="<?= $e($r['tanggal_selesai']) ?>"></td>
    <td><select name="<?= $n ?>[status]" class="st-<?= $st ?>"><?php foreach (Tender::STATUS as $s): ?><option <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></td>
  <?php endif; ?>
  </tr>
  <?php endforeach; ?></tbody></table>
  <?php if ($canEdit): ?>
  <div class="row between">
    <button class="btn outline" type="submit" name="action" value="add">+ Tambah Tahap</button>
    <button class="btn wide" type="submit" name="action" value="save">Simpan</button>
  </div>
  <?php else: ?><p class="hint ro">Anda login sebagai <?= $e($user['role']) ?>: halaman hanya-baca.</p><?php endif; ?>
  </form>
<?php endif; ?>
</section>

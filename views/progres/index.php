<h1>Progres Pengadaan</h1>
<p class="hint">Klik salah satu paket untuk melihat tahapan tendernya</p>
<table class="clickable"><thead><tr><th>Nama Paket</th><th>Metode</th><th>Status</th><th>Progres (tahap selesai / <?= count(Tender::TAHAP) ?>)</th></tr></thead><tbody>
<?php if (!$list): ?><tr><td colspan="4" class="muted">Belum ada data</td></tr><?php endif; ?>
<?php foreach ($list as $p): $pct = round($p['tahap_selesai'] / count(Tender::TAHAP) * 100); ?>
<tr class="<?= $sel && $sel['id'] == $p['id'] ? 'sel' : '' ?>" onclick="location='index.php?page=progres&id=<?= $p['id'] ?>'">
<td><a href="index.php?page=progres&id=<?= $p['id'] ?>"><?= $e($p['nama_paket']) ?></a></td><td><?= $e($p['metode']) ?></td>
<td class="st-<?= strtolower($p['status']) ?>"><?= $e($p['status']) ?></td>
<td><div class="bar"><i style="width:<?= $pct ?>%"></i></div> <?= $pct ?>%</td></tr>
<?php endforeach; ?></tbody></table>
<?php if ($sel): ?>
<h2>Detail Progres: <?= $e($sel['nama_paket']) ?> (<?= $sel['tahap_selesai'] ?> dari <?= count(Tender::TAHAP) ?> tahap selesai)</h2>
<div class="panel"><?php require __DIR__ . '/stepper.php'; ?></div>
<?php endif; ?>
<div class="note">Halaman hanya-baca. Jika belum ada paket: tampil "Belum ada data"</div>

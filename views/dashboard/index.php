<div class="cards">
  <div class="card c-blue"><span>Paket Aktif</span><b><?= $aktif ?></b></div>
  <div class="card c-green"><span>Sedang Tender</span><b><?= $tender ?></b></div>
  <div class="card c-orange"><span>Selesai</span><b><?= $selesai ?></b></div>
  <div class="card c-red"><span>Total Pengguna</span><b><?= $totalUser ?></b></div>
</div>
<h2>Progres Pengadaan Terbaru</h2>
<table><thead><tr><th>Nama Paket</th><th>Status</th><th>Tahap</th></tr></thead><tbody>
<?php if (!$terbaru): ?><tr><td colspan="3" class="muted">Belum ada data</td></tr><?php endif; ?>
<?php foreach ($terbaru as $p): ?>
<tr><td><a href="index.php?page=progres&id=<?= $p['id'] ?>"><?= $e($p['nama_paket']) ?></a></td>
<td class="st-<?= strtolower($p['status']) ?>"><?= $e($p['status']) ?></td><td><?= $e($p['tahap_aktif'] ?? '-') ?></td></tr>
<?php endforeach; ?></tbody></table>

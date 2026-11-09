<div class="stepper">
<?php foreach ($stages as $i => $s): $cls = ['Selesai' => 'done', 'Berjalan' => 'run'][$s['status']] ?? 'todo'; ?>
  <div class="step <?= $cls ?>"><div class="dot"><?= $cls === 'done' ? '✓' : $i + 1 ?></div>
  <div class="cap"><?= $e($s['tahap']) ?><br><?= $e($s['status']) ?></div></div>
<?php endforeach; ?>
</div>
<p class="legend">Hijau = selesai | Oranye = berjalan | Abu-abu = belum dimulai</p>

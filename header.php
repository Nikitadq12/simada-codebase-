<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
/** Badge ketepatan waktu: Lebih cepat / Tepat waktu / Delay N hari */
$kt = fn(?array $k) => $k ? '<span class="kt kt-' . $k['kode'] . '">' . htmlspecialchars($k['label']) . '</span>' : '<span class="muted">-</span>'; ?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>SIMADA</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="<?= $user ? 'app' : 'guest' ?>">
<?php if ($user): ?>
<header class="topbar"><span class="brand">SIMADA</span>
  <span class="who"><?= $e($user['nama']) ?> (<?= $e($user['role']) ?>) | <a href="index.php?page=logout">Logout</a></span></header>
<aside class="sidebar">
<?php foreach ([['dashboard','Dashboard',true],['users','Pengguna',$user['role']==='Admin'],['tender','e-Tendering',true],['progres','Progres',true]] as [$k,$label,$show]): if ($show): ?>
  <a class="nav<?= $active === $k ? ' active' : '' ?>" href="index.php?page=<?= $k ?>"><?= $label ?></a>
<?php endif; endforeach; ?>
</aside>
<main class="content">
<?php if (!empty($_SESSION['flash'])): ?><div class="alert ok"><?= $e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?>
<?php else: ?><main><?php endif; ?>

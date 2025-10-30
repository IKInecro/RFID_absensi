<?php
// pages/schedule.php
// Full replace: modern dark UI, preserve add/edit/delete schedule functionality.
// Place at pages/schedule.php

include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// fetch schedules
$res = $conn->query("SELECT * FROM schedules ORDER BY FIELD(day,'Mon','Tue','Wed','Thu','Fri','Sat','Sun'), time_in ASC");

// edit support
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_data = null;
if ($edit_id) {
    $r = $conn->query("SELECT * FROM schedules WHERE id={$edit_id} LIMIT 1");
    if ($r && $r->num_rows) $edit_data = $r->fetch_assoc();
}

// days list (user-friendly but store in DB likely 3-letter D format)
$days = ['Mon'=>'Mon','Tue'=>'Tue','Wed'=>'Wed','Thu'=>'Thu','Fri'=>'Fri','Sat'=>'Sat','Sun'=>'Sun'];

function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="id" data-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Schedule — Jadwal</title>
<style>
:root{--bg:#071026;--panel:#071a2b;--muted:#9bb0c9;--text:#e6f0fb;--accent:#0D47A1}
html,body{background:var(--bg);color:var(--text);font-family:Inter,system-ui,Segoe UI,Roboto,Arial;margin:0;padding:0}
.container{max-width:1100px;margin:28px auto;padding:18px}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.title{font-size:20px;font-weight:700}
.card{background:var(--panel);border-radius:12px;padding:12px;border:1px solid rgba(255,255,255,0.03)}
.form-inline{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
input[type="text"], input[type="time"], select { background:#0b1624;color:var(--text);border:1px solid rgba(255,255,255,0.04);padding:8px;border-radius:8px }
.btn{background:var(--accent);color:white;padding:8px 12px;border-radius:8px;border:0;cursor:pointer}
.list{display:flex;flex-direction:column;gap:10px;margin-top:12px}
.item{display:flex;justify-content:space-between;align-items:center;padding:10px;border-radius:10px;background:linear-gradient(180deg, rgba(255,255,255,0.01), transparent);border:1px solid rgba(255,255,255,0.02);transition:transform .12s ease, box-shadow .12s ease}
.item:hover{transform:translateY(-6px);box-shadow:0 14px 40px rgba(6,20,40,.6)}
.meta{display:flex;flex-direction:column}
.day{font-weight:700}
.small{color:var(--muted)}
.actions{display:flex;gap:8px}
.icon{background:rgba(255,255,255,0.03);padding:6px;border-radius:8px;color:var(--text)}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <div class="title">Jadwal</div>
      <div class="small">Atur jam masuk & pulang — interface modern gelap</div>
    </div>
    <div>
      <a href="index.php?page=schedule" class="btn">Segarkan</a>
    </div>
  </div>

  <div class="card">
    <h3 style="margin:0 0 8px 0"><?= $edit_data ? 'Edit Jadwal' : 'Tambah Jadwal' ?></h3>
    <form action="action_schedule.php" method="post" class="form-inline">
      <input type="hidden" name="id" value="<?= esc($edit_data['id'] ?? '') ?>">
      <div>
        <label class="small">Hari</label><br>
        <select name="day" required>
          <?php foreach ($days as $k=>$v): ?>
            <option value="<?= esc($k) ?>" <?= (isset($edit_data['day']) && $edit_data['day']==$k)?'selected':'' ?>><?= esc($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="small">Jam Masuk</label><br>
        <input type="time" name="time_in" required value="<?= esc($edit_data['time_in'] ?? '') ?>">
      </div>
      <div>
        <label class="small">Jam Pulang</label><br>
        <input type="time" name="time_out" required value="<?= esc($edit_data['time_out'] ?? '') ?>">
      </div>
      <div>
        <label class="small">Toleransi (menit)</label><br>
        <input type="text" name="grace_period" value="<?= esc($edit_data['grace_period'] ?? '0') ?>">
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <label class="small">Libur</label>
        <select name="is_holiday">
          <option value="0" <?= (isset($edit_data['is_holiday']) && intval($edit_data['is_holiday'])===1)?'':'selected' ?>>No</option>
          <option value="1" <?= (isset($edit_data['is_holiday']) && intval($edit_data['is_holiday'])===1)?'selected':'' ?>>Yes</option>
        </select>
      </div>
      <div style="margin-left:auto">
        <button type="submit" name="<?= $edit_data ? 'update' : 'create' ?>" class="btn"><?= $edit_data ? 'Update' : 'Simpan' ?></button>
        <?php if ($edit_data): ?><a href="index.php?page=schedule" class="btn btn.ghost">Batal</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="list">
    <?php if ($res && $res->num_rows): while ($r = $res->fetch_assoc()): ?>
      <div class="item">
        <div class="meta">
          <div class="day"><?= esc($r['day']) ?> • <?= esc($r['time_in']) ?> - <?= esc($r['time_out']) ?></div>
          <div class="small">Toleransi: <?= esc($r['grace_period']) ?> menit • <?= intval($r['is_holiday']) ? 'Libur' : 'Tidak Libur' ?></div>
        </div>
        <div class="actions">
          <a href="index.php?page=schedule&edit=<?= intval($r['id']) ?>" class="icon">✏️</a>
          <a href="action_schedule.php?delete=<?= intval($r['id']) ?>" onclick="return confirm('Hapus jadwal ini?')" class="icon">🗑️</a>
        </div>
      </div>
    <?php endwhile; else: ?>
      <div class="card placeholder">Belum ada jadwal</div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>

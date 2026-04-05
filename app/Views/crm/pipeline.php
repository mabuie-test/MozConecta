<?php ob_start(); ?>
<h1>Pipeline Comercial Visual</h1>
<div class="pipeline-grid">
  <?php foreach ($stages as $stage): ?>
    <section class="pipeline-col card">
      <h3><?= htmlspecialchars((string)$stage['name']) ?></h3>
      <?php foreach (($contactsByStage[(int)$stage['id']] ?? []) as $contact): ?>
        <article class="pipeline-card">
          <strong><?= htmlspecialchars((string)$contact['display_name']) ?></strong>
          <div class="muted"><?= htmlspecialchars((string)$contact['phone']) ?></div>
          <div>Score: <strong><?= (int)($contact['lead_score'] ?? 0) ?></strong> | Prioridade: <?= htmlspecialchars((string)$contact['priority']) ?></div>
          <form method="post" action="/crm/pipeline/move">
            <input type="hidden" name="contact_id" value="<?= (int)$contact['id'] ?>">
            <select name="stage_id">
              <?php foreach ($stages as $target): ?>
                <option value="<?= (int)$target['id'] ?>" <?= ((int)$target['id'] === (int)$stage['id']) ? 'selected' : '' ?>><?= htmlspecialchars((string)$target['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit">Mover</button>
          </form>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endforeach; ?>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>

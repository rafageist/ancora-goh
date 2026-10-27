<?php
/** @var callable $e */ /** @var array<string,string> $errors */ /** @var array<string,mixed> $input */
/** @var array<string,string> $roles */ /** @var array<string,string> $types */ /** @var array<string,string> $countries */
/** @var string $token */ /** @var string $honeypot */
$v = fn (string $k): string => is_string($input[$k] ?? null) ? $input[$k] : '';
$err = fn (string $k): string => isset($errors[$k]) ? '<p class="error" id="err-' . $e($k) . '">' . $e($errors[$k]) . '</p>' : '';
$sel = fn (string $k, string $opt): string => $v($k) === $opt ? ' selected' : '';
?>
<h1>Request access</h1>
<p>Every request is reviewed by a person. We only ask for what we need to review it; see the
<a href="/privacy">privacy notice</a>. Do not include information about students.</p>
<?php if (isset($errors['form'])): ?><p class="error"><?= $e($errors['form']) ?></p><?php endif; ?>
<?php if ($errors !== [] && !isset($errors['form'])): ?><p class="error">Please correct the fields marked below.</p><?php endif; ?>
<form method="post" action="/request" novalidate>
  <input type="hidden" name="form_token" value="<?= $e($token) ?>">
  <label for="full_name">Your name</label>
  <input id="full_name" name="full_name" maxlength="120" required autocomplete="name" value="<?= $e($v('full_name')) ?>">
  <?= $err('full_name') ?>
  <label for="email">Email address</label>
  <input id="email" name="email" type="email" maxlength="254" required autocomplete="email" value="<?= $e($v('email')) ?>">
  <?= $err('email') ?>
  <label for="organization">Organization or project</label>
  <input id="organization" name="organization" maxlength="160" required autocomplete="organization" value="<?= $e($v('organization')) ?>">
  <?= $err('organization') ?>
  <label for="organization_type">Kind of organization</label>
  <select id="organization_type" name="organization_type" required>
    <option value="">Choose…</option>
    <?php foreach ($types as $k => $label): ?><option value="<?= $e($k) ?>"<?= $sel('organization_type', $k) ?>><?= $e($label) ?></option><?php endforeach; ?>
  </select>
  <?= $err('organization_type') ?>
  <label for="country">Country or region</label>
  <select id="country" name="country" required>
    <option value="">Choose…</option>
    <?php foreach ($countries as $k => $label): ?><option value="<?= $e($k) ?>"<?= $sel('country', $k) ?>><?= $e($label) ?></option><?php endforeach; ?>
  </select>
  <?= $err('country') ?>
  <label for="role">Your role</label>
  <select id="role" name="role" required>
    <option value="">Choose…</option>
    <?php foreach ($roles as $k => $label): ?><option value="<?= $e($k) ?>"<?= $sel('role', $k) ?>><?= $e($label) ?></option><?php endforeach; ?>
  </select>
  <?= $err('role') ?>
  <label for="intended_use">How do you plan to use the service?</label>
  <textarea id="intended_use" name="intended_use" maxlength="1000" rows="5" required><?= $e($v('intended_use')) ?></textarea>
  <?= $err('intended_use') ?>
  <label for="website">Organization website (optional)</label>
  <input id="website" name="website" type="url" maxlength="200" value="<?= $e($v('website')) ?>">
  <?= $err('website') ?>
  <div class="hp" aria-hidden="true">
    <label for="<?= $e($honeypot) ?>">Leave this field empty</label>
    <input id="<?= $e($honeypot) ?>" name="<?= $e($honeypot) ?>" tabindex="-1" autocomplete="off" value="">
  </div>
  <label class="check"><input type="checkbox" name="privacy" value="accepted"<?= $v('privacy') === 'accepted' ? ' checked' : '' ?>>
    I have read the <a href="/privacy">privacy notice</a> and agree that my request is processed as described there.</label>
  <?= $err('privacy') ?>
  <button type="submit">Send request</button>
</form>

<?php /** @var callable $e */ /** @var string $token */ /** @var string $verification */ ?>
<h1>Confirm your email</h1>
<p>Press the button to confirm the email address of your access request.</p>
<form method="post" action="/verify">
  <input type="hidden" name="form_token" value="<?= $e($token) ?>">
  <input type="hidden" name="verification" value="<?= $e($verification) ?>">
  <button type="submit">Confirm</button>
</form>

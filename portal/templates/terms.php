<?php /** @var callable $e */ /** @var string $contact */ ?>
<h1>Terms of the access request</h1>
<ul>
  <li>Sending a request does not create an account or give access to the service. Every request is reviewed individually
  and may be approved, rejected, or answered with a request for more information.</li>
  <li>The service is free. Access is offered as an invitation-only beta, without guarantees of availability, and may be
  limited by fair-use quotas.</li>
  <li>Provide truthful information about yourself and your organization. Requests made in someone else's name will be rejected.</li>
  <li>Do not send information about students or other third parties through this form.</li>
  <li>Questions: <a href="mailto:<?= $e($contact) ?>"><?= $e($contact) ?></a>.</li>
</ul>

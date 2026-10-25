<?php /** @var callable $e */ /** @var string $version */ /** @var string $controller */ /** @var string $contact */ ?>
<h1>Privacy notice</h1>
<p class="meta">Version <?= $e($version) ?>. Applies to access requests sent through this site.</p>

<h2>Who is responsible</h2>
<p>The data controller is <?= $e($controller) ?>. For any question or request about your data, write to
<a href="mailto:<?= $e($contact) ?>"><?= $e($contact) ?></a>.</p>

<h2>What we collect and why</h2>
<p>To review your request for access to the free Divengine Timetables service, we collect only:</p>
<ul>
  <li>your name and email address, to identify the request and write to you;</li>
  <li>your organization or project, its kind, its optional website, your country or region and your role, to assess the request;</li>
  <li>your description of the intended use, to decide whether the service fits it;</li>
  <li>the date of your consent and the version of this notice.</li>
</ul>
<p>We do not ask for identity documents, dates of birth, addresses, phone numbers or any information about students.
Please do not include such information in your description.</p>
<p>For security we keep anonymous counters to limit abuse (derived from your IP address and email address with a
keyed hash; the addresses themselves are not stored with the counters) and technical logs without your email address in full.</p>

<h2>Legal basis</h2>
<p>We process this data to take the steps you asked for before granting access (reviewing your request), and on the basis
of your consent given on the form. You can withdraw consent at any time by writing to us; we then delete your request.</p>

<h2>Who receives it</h2>
<p>Only the people who review requests. Emails are delivered through a transactional email provider acting on our behalf.
We do not sell or share your data and do not use it for advertising.</p>

<h2>How long we keep it</h2>
<ul>
  <li>Requests whose email is not confirmed: deleted after 7 days.</li>
  <li>Rejected or cancelled requests: deleted 90 days after the decision.</li>
  <li>Approved requests: kept while you may use the service; deleted when you leave it.</li>
</ul>
<p>A record of each decision (date, status and reviewer, without your personal details) is kept for accountability.</p>

<h2>Your rights</h2>
<p>You can ask for access to, correction or deletion of your data, object to its processing, or withdraw consent, by writing to
<a href="mailto:<?= $e($contact) ?>"><?= $e($contact) ?></a>. You may also complain to your data protection authority.</p>

<h2>Security</h2>
<p>The site uses HTTPS only. Requests are stored in a database reachable only from the server. Confirmation links are
single-use and expire after 24 hours.</p>

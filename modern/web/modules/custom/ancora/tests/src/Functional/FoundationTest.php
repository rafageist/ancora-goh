<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora\Functional;

use PHPUnit\Framework\Attributes\Group;

/**
 * End-to-end proof of the foundation through HTTP.
 *
 * Covers the permission-protected status route rendered by the theme's
 * component, the academic term entity form, its administrative list and the
 * Views listing.
 */
#[Group('ancora')]
final class FoundationTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora', 'block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Anonymous users are denied; operators see the status card.
   */
  public function testStatusPage(): void {
    $this->drupalGet('/ancora/status');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($this->drupalCreateUser(['view ancora status']));
    $this->drupalGet('/ancora/status');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', 'section.status-card');
    $this->assertSession()->elementTextContains('css', '.status-card__heading', 'Áncora platform');
    $this->assertSession()->pageTextContains(\Drupal::VERSION);
  }

  /**
   * Administrators create terms; viewers see them in the Views listing.
   */
  public function testAcademicTermLifecycle(): void {
    $this->drupalGet('/admin/content/ancora-term');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('/ancora/terms');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($this->drupalCreateUser(['administer ancora academic terms']));
    $this->drupalGet('/admin/content/ancora-term');
    $this->assertSession()->statusCodeEquals(200);
    $this->clickLink('Add academic term');

    // A term whose last day precedes its first day is refused.
    $this->submitForm([
      'label[0][value]' => 'Autumn 2026',
      'code[0][value]' => '2026-A',
      'starts_on[0][value][date]' => '2026-09-01',
      'ends_on[0][value][date]' => '2026-08-31',
    ], 'Save');
    $this->assertSession()->pageTextContains('must not be before the first day');

    $this->submitForm(['ends_on[0][value][date]' => '2026-12-18'], 'Save');
    $this->assertSession()->pageTextContains('Created the academic term Autumn 2026.');
    $this->assertSession()->addressEquals('/admin/content/ancora-term');
    $this->assertSession()->elementTextContains('css', 'table', '2026-A');

    $this->drupalLogin($this->drupalCreateUser(['view ancora academic terms']));
    $this->drupalGet('/ancora/terms');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementTextContains('css', 'table', 'Autumn 2026');
    $this->assertSession()->elementTextContains('css', 'table', '2026-12-18');
    $this->drupalGet('/admin/content/ancora-term');
    $this->assertSession()->statusCodeEquals(403);
  }

}

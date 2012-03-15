<?php

/**
 * PHP unit tests
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

require_once PATH_STANDARD . '/app/controllers/Main.controller.php';
require_once PATH_STANDARD . '/app/controllers/Calendars.controller.php';
require_once PATH_STANDARD . '/app/helpers/SmartySingleton.helper.php';

use \CandyCMS\Controller\Calendars as Calendars;
use \CandyCMS\Helper\I18n as I18n;

class WebTestOfCalendarsController extends CandyWebTest {

	function setUp() {
		$this->aRequest['controller'] = 'calendars';
		$this->oObject = new Calendars($this->aRequest, $this->aSession);
	}

	function tearDown() {
		parent::tearDown();
	}

	function testShowOverview() {
		$this->assertTrue($this->get(WEBSITE_URL . '/' . $this->aRequest['controller']));
		$this->assertText('8f9e4a9962');
    $this->assertResponse(200);
	}

  function testShowArchive() {
		$this->assertTrue($this->get(WEBSITE_URL . '/' . $this->aRequest['controller'] . '/2020/archive'));
		$this->assertText('8f9e4a9962');
		$this->assertText('2019');
		$this->assertText('2021');
    $this->assertText(I18n::get('global.archive'));
    $this->assertResponse(200);
  }

  function testShowWithId() {
    //get an entry
		$this->assertTrue($this->get(WEBSITE_URL . '/' . $this->aRequest['controller'] . '/2'));
		$this->assertText('BEGIN:VCALENDAR');
    $this->assertText('SUMMARY:8f9e4a9962');
    $this->assertHeader('Content-type', 'text/calendar; charset=utf-8');
    $this->assertResponse(200);

    //get a missing entry
		$this->assertTrue($this->get(WEBSITE_URL . '/' . $this->aRequest['controller'] . '/42'));
    $this->assertResponse(200);
    $this->assertText(I18n::get('error.missing.id'));
	}

	function testCreate() {
    //TODO
	}

	function testUpdate() {
    //TODO
	}

	function testDestroy() {
		//TODO
	}
}
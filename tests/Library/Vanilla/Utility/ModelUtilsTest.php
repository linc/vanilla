<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\Library\Vanilla\Utility;

use Gdn_Validation;
use Garden\Schema\Validation;
use Garden\Schema\ValidationException;
use Iterator;
use PHPUnit\Framework\TestCase;
use Vanilla\Utility\ModelUtils;

/**
 * Tests for Vanilla\Utility\ModelUtilsTest class.
 */
class ModelUtilsTest extends TestCase
{
    /**
     * Test converting a Garden Schema exception into its Gdn_Validation equivalent.
     */
    public function testValidationExceptionToValidationResult()
    {
        $validation = new Validation();
        $validation->addError("name", "name is required.");
        $validation->addError("email", "email is required.");
        $exception = new ValidationException($validation);

        $expected = new Gdn_Validation();
        $expected->addValidationResult("name", "%s is required.");
        $expected->addValidationResult("email", "%s is required.");

        $actual = ModelUtils::validationExceptionToValidationResult($exception);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Verify iterateWithTimeout times out and returns a value indicating an incomplete run.
     */
    public function testTimeoutIterateWithTimeout(): void
    {
        $i = 0;
        $iterator = function () use (&$i): Iterator {
            do {
                $i++;
                yield true;
            } while (true);
        };
        $result = ModelUtils::consumeGenerator(ModelUtils::iterateWithTimeout($iterator(), 1));
        $this->assertGreaterThan(0, $i);
        $this->assertSame(false, $result);
    }

    /**
     * Verify completing all iterations within a timeout returns a successful result.
     */
    public function testCompletedIterateWithTimeout(): void
    {
        $i = 0;
        $iterator = function () use (&$i): Iterator {
            $i++;
            yield true;
        };
        $result = ModelUtils::consumeGenerator(ModelUtils::iterateWithTimeout($iterator(), 50));
        $this->assertSame(1, $i);
        $this->assertSame(true, $result);
    }

    /**
     * Test automatic slot type determinations.
     *
     * @return void
     */
    public function testGetDateBasedSlotType(): void
    {
        $this->assertEquals(
            "d",
            ModelUtils::getDateBasedSlotType(postDate: "2019-01-01T00:00:00", now: "2019-01-01T23:00:00")
        );
        $this->assertEquals("w", ModelUtils::getDateBasedSlotType(postDate: "2019-01-01", now: "2019-01-07"));
        $this->assertEquals("m", ModelUtils::getDateBasedSlotType(postDate: "2019-01-01", now: "2019-01-31"));
        $this->assertEquals("y", ModelUtils::getDateBasedSlotType(postDate: "2019-01-01", now: "2019-12-31"));
        $this->assertEquals("a", ModelUtils::getDateBasedSlotType(postDate: "2019-01-01", now: "2020-03-01"));
    }
}

<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Synod\BugReport\Tests\Validate;

use Assert\Assertion;
use PHPUnit\Framework\TestCase;
use Synod\BugReport\Exception\ValidationException;
use Synod\BugReport\Validate\Validator;

/**
 * @backupStaticAttributes enabled
 *
 * @covers \Synod\BugReport\Validate\Validator
 */
final class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    /**
     * @runInSeparateProcess (for complete coverage of the constructor when this test class is run together with other tests)
     *
     * @covers \Synod\BugReport\Validate\Validator::validateFile
     */
    public function testValidateFile(): void
    {
        $this->validator->validateFile('log', 'logs.0.txt', 'plain text');

        $image = file_get_contents(__DIR__.'/../_data/image.png');
        Assertion::string($image);
        $this->validator->validateFile('file', 'screenshot.png', $image);

        $gzip = file_get_contents(__DIR__.'/../_data/empty.gz');
        Assertion::string($gzip);
        $this->validator->validateFile('compressed_log', 'logcat.log.gz', $gzip);

        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFile
     */
    public function testValidateFileMediaTypeForbidden(): void
    {
        Validator::$fileAllowedMediaTypes = ['image/png'];
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Media type "text/plain" is not an allowed media type for files');
        $this->validator->validateFile('file', 'screenshot.png', 'plain text');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFile
     */
    public function testValidateFilenameEmpty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A filename must not be empty');
        $this->validator->validateFile('file', '', 'plain text');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFile
     */
    public function testValidateFilenameForbidden(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Value "forbidden*filename" is not an allowed filename');
        $this->validator->validateFile('file', 'forbidden*filename', 'plain text');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFile
     */
    public function testValidateFilenameTooLong(): void
    {
        Validator::$filenameMaxLength = 3;
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A filename must not have more than 3 characters');
        $this->validator->validateFile('log', 'name', 'plain text');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFile
     */
    public function testValidateFileKeyEmpty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A key must not be empty');
        $this->validator->validateFile('', 'logs.0.txt', 'plain text');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFile
     */
    public function testValidateFileKeyInvalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A key must only contain printable ASCII characters');
        $this->validator->validateFile("invalid\tkey", 'logs.0.txt', 'plain text');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFile
     */
    public function testValidateFileKeyTooLong(): void
    {
        Validator::$keyMaxLength = 3;
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A key must not have more than 3 characters');
        $this->validator->validateFile('1234', 'logs.0.txt', 'plain text');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFile
     */
    public function testValidateFileTooLong(): void
    {
        Validator::$fileMaxSize = 2;
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A file must not have more than 2 bytes');
        $this->validator->validateFile('log', 'logs.0.txt', '123');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFinalFiles
     */
    public function testValidateFinalFiles(): void
    {
        $this->validator->validateFinalFiles([]);
        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFinalLabels
     */
    public function testValidateFinalLabels(): void
    {
        $this->validator->validateFinalLabels(['test']);
        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFinalLabels
     */
    public function testValidateFinalLabelsNoLabel(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('At least one label is required');
        $this->validator->validateFinalLabels([]);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFinalValues
     */
    public function testValidateFinalValues(): void
    {
        Validator::$valueKeysRequired = ['required'];
        $this->validator->validateFinalValues(['required' => 'value']);
        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFinalValues
     */
    public function testValidateFinalValuesRequiredMissing(): void
    {
        Validator::$valueKeysRequired = ['required'];
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A value for "required" is missing');
        $this->validator->validateFinalValues(['key' => 'value']);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateLabel
     */
    public function testValidateLabel(): void
    {
        $this->validator->validateLabel('test');
        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateLabel
     */
    public function testValidateLabelEmpty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A label must not be empty');
        $this->validator->validateLabel('');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateLabel
     */
    public function testValidateLabelInvalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A label must only contain printable ASCII characters');
        $this->validator->validateLabel("invalid\tlabel");
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateLabel
     */
    public function testValidateLabelTooLong(): void
    {
        Validator::$labelMaxLength = 2;
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A label must not have more than 2 characters');
        $this->validator->validateLabel('123');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValue
     */
    public function testValidateValue(): void
    {
        $this->validator->validateValue('key', 'value');
        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValue
     */
    public function testValidateKeyEmpty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A key must not be empty');
        $this->validator->validateValue('', 'value');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValue
     */
    public function testValidateKeyInvalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A key must only contain printable ASCII characters');
        $this->validator->validateValue("invalid\tkey", 'value');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValue
     */
    public function testValidateKeyTooLong(): void
    {
        Validator::$keyMaxLength = 2;
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A key must not have more than 2 characters');
        $this->validator->validateValue('123', 'value');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValue
     */
    public function testValidateValueInvalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A value for key "key" may only contain printable ASCII');
        $this->validator->validateValue('key', "invalid\tvalue");
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValue
     */
    public function testValidateValueTooLong(): void
    {
        Validator::$valueMaxLength = 3;
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A value for key "key" must not have more than 3 characters');
        $this->validator->validateValue('key', '1234');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValue
     */
    public function testValidateValueText(): void
    {
        $this->validator->validateValue('text', "text with tab\t, carriage return\r, and line break\n");
        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValue
     */
    public function testValidateValueTextInvalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A value for key "text" must not contain control code characters, but \r, \n, and \t');
        $this->validator->validateValue('text', "text with vertical tab\v");
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValue
     */
    public function testValidateValueTextTooLong(): void
    {
        Validator::$textMaxLengths = 4;
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A value for key "text" must not have more than 4 characters');
        $this->validator->validateValue('text', '12345');
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFileCount
     */
    public function testValidateFileCount(): void
    {
        Validator::$fileMaxCount = 11;
        $this->validator->validateFileCount(11);
        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateFileCount
     */
    public function testValidateFileCountExceeded(): void
    {
        Validator::$fileMaxCount = 11;
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('More than 11 files are not allowed');
        $this->validator->validateFileCount(12);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateLabelCount
     */
    public function testValidateLabelCount(): void
    {
        Validator::$labelMaxCount = 22;
        $this->validator->validateLabelCount(22);
        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateLabelCount
     */
    public function testValidateLabelCountExceeded(): void
    {
        Validator::$labelMaxCount = 22;
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('More than 22 labels are not allowed');
        $this->validator->validateLabelCount(23);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValueCount
     */
    public function testValidateValueCount(): void
    {
        Validator::$valueMaxCount = 33;
        $this->validator->validateValueCount(33);
        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validator::validateValueCount
     */
    public function testValidateValueCountExceeded(): void
    {
        Validator::$valueMaxCount = 33;
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('More than 33 values are not allowed');
        $this->validator->validateValueCount(34);
    }
}

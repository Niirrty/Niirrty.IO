<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license    MIT
 * @package    Niirrty\Tests\IO
 * @since      2026-02-14
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Tests\IO;


use Niirrty\IO\FileAlreadyExistsException;
use Niirrty\IO\FileFormatException;
use Niirrty\IO\FileNotFoundException;
use Niirrty\IO\FolderAccessException;
use Niirrty\IO\FolderNotFoundException;
use Niirrty\IO\IOException;
use Niirrty\NiirrtyException;
use Niirrty\PhpException;
use PHPUnit\Framework\TestCase;


/**
 * Test case for the class `Niirrty\IO\FileNotFoundException`.
 *
 * Findings see `journal/05-file-not-found-exception.md`: the source is NOT
 * changed, the tests assert the CURRENT behaviour. In contrast to the sibling
 * `Niirrty\IO\FileAlreadyExistsException` this class provides its own fixed
 * `IOException` text 'File not exists.' instead of the text 'The File does
 * already exist.'. Like that sibling all optional parameters are explicitly
 * nullable (`?string $message`, `?\Throwable $previous`) so - in contrast to
 * `FileAccessException` - the FIRST use of the class definition does NOT raise
 * a deprecation notice and works as usual. Nevertheless this test must stay the
 * first test method to document that. Only the default `$code` differs: this
 * class uses the constant `\E_USER_ERROR` instead of the literal `256` - which
 * is the very same value.
 *
 * @since v0.1.0
 */
class FileNotFoundExceptionTest extends TestCase
{


    #region // = = =   T E S T   M E T H O D S  = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Test the FIRST use of the class definition: the class itself is a plain,
     * thin `IOException` sub-class without soft-nullable parameter so the class
     * definition causes no PHP 8.5 deprecation notice and no `Niirrty\PhpException`
     * is thrown when the class is used the very first time.
     *
     * NOTE the default `$code` of this class is the constant `\E_USER_ERROR`
     * (in contrast to the literal `256` of e.g. `Niirrty\IO\FileAlreadyExistsException`)
     * - and `\E_USER_ERROR` is NOT one of the constants deprecated in PHP 8.4/8.5.
     *
     * This test must stay the FIRST test method to document that behaviour.
     *
     * @since v0.1.0
     */
    public function testFirstUseOfClassDefinition()
    {

        $ex = new FileNotFoundException( '/probe/first.txt' );

        $this->assertInstanceOf( FileNotFoundException::class, $ex );
        $this->assertSame( '/probe/first.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/probe/first.txt]! File not exists.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( \E_USER_ERROR, $ex->getCode() );

    }

    /**
     * Test the class hierarchy: a `FileNotFoundException` is an
     * `Niirrty\IO\IOException`, a `Niirrty\NiirrtyException`, an `\Exception`
     * and a `\Throwable`, but the parent classes are NOT `FileNotFoundException`s.
     *
     * @since v0.1.0
     */
    public function testClassHierarchy()
    {

        $ex = new FileNotFoundException( '/probe/file.txt' );

        // instanceof chain to the base classes:
        $this->assertInstanceOf( FileNotFoundException::class, $ex );
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( NiirrtyException::class, $ex );
        $this->assertInstanceOf( \Exception::class, $ex );
        $this->assertTrue( $ex instanceof \Throwable );
        // A special IO exception class of this package also is throwable.
        $this->assertTrue( $ex instanceof FileNotFoundException );

        // Direct and indirect parent classes:
        $this->assertSame( 'Niirrty\IO\IOException', (string) \get_parent_class( $ex ) );
        $this->assertSame( 'Niirrty\NiirrtyException', (string) \get_parent_class( new IOException( '/probe/file.txt' ) ) );
        $this->assertSame( 'Niirrty\IO\FileNotFoundException', (string) \get_class( $ex ) );

        // An ordinary `IOException` is NO `FileNotFoundException`:
        $ioex = new IOException( '/probe/file.txt' );
        $this->assertNotInstanceOf( FileNotFoundException::class, $ioex );
        $this->assertSame( 'There was a error with path [/probe/file.txt]!', $ioex->getMessage() );

        // A `NiirrtyException` also is no `FileNotFoundException`:
        $nex = new NiirrtyException( 'some error', 512 );
        $this->assertNotInstanceOf( FileNotFoundException::class, $nex );
        $this->assertNotInstanceOf( IOException::class, $nex );

    }

    /**
     * Test the (complete) instanceof chain incl. the sibling exception classes
     * of the `Niirrty\IO` package: a `FileNotFoundException` is NONE of them,
     * because every one of them provides its own, fixed message text.
     *
     * @since v0.1.0
     */
    public function testInstanceofChain()
    {

        $ex = new FileNotFoundException( '/probe/file.txt' );

        $this->assertInstanceOf( FileNotFoundException::class, $ex );
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( NiirrtyException::class, $ex );
        $this->assertInstanceOf( \Exception::class, $ex );
        $this->assertTrue( is_object( $ex ) );
        $this->assertTrue( $ex instanceof \Throwable );

        // All sibling exception classes of the `Niirrty\IO` package:
        $this->assertNotInstanceOf( FileAlreadyExistsException::class, $ex );
        $this->assertNotInstanceOf( FileFormatException::class, $ex );
        $this->assertNotInstanceOf( FolderAccessException::class, $ex );
        $this->assertNotInstanceOf( FolderNotFoundException::class, $ex );

    }

    /**
     * Test, that a `FileNotFoundException` is NOT one of the sibling classes and
     * a sibling class is NOT a `FileNotFoundException`, even when the inner
     * `Niirrty\IO\IOException` (and therefore the `getPath()` behaviour) is equal.
     *
     * @since v0.1.0
     */
    public function testNotInstanceOfSiblings()
    {

        $ex = new FileNotFoundException( '/f.txt' );
        $sibling = new FileAlreadyExistsException( '/f.txt' );

        $this->assertNotInstanceOf( FileAlreadyExistsException::class, $ex );
        $this->assertNotInstanceOf( FileNotFoundException::class, $sibling );

        // Both ARE `IOException`s, but they keep their own fixed message text:
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( IOException::class, $sibling );
        $this->assertSame( 'There was a error with path [/f.txt]! File not exists.', $ex->getMessage() );
        $this->assertSame( 'There was a error with path [/f.txt]! The File does already exist.', $sibling->getMessage() );

        // Both share the same, inherited `getPath()` behaviour:
        $this->assertSame( '/f.txt', $ex->getPath() );
        $this->assertSame( '/f.txt', $sibling->getPath() );

    }

    /**
     * Test the constructor with the file parameter only: the file must be kept
     * as path (inherited `getPath()`), the own fixed text 'File not exists.'
     * must be appended to the `IOException` message - handed over as `$message`
     * parameter to the parent constructor - and the code must default to the
     * constant `\E_USER_ERROR` (256).
     *
     * @since v0.1.0
     */
    public function testConstructorDefaults()
    {

        $ex = new FileNotFoundException( '/probe/file.txt' );

        $this->assertSame( '/probe/file.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/probe/file.txt]! File not exists.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( \E_USER_ERROR, $ex->getCode() );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // Each call returns a new, independent instance:
        $ex2 = new FileNotFoundException( '/probe/file.txt' );
        $this->assertNotEquals( $ex, $ex2 );
        $this->assertNotSame( $ex, $ex2 );

    }

    /**
     * Test the `$file` parameter and the inherited `getPath()`: the file must
     * always be returned "as is", also one with spaces, umlauts and backslashes,
     * a blank and an empty file are accepted.
     *
     * @since v0.1.0
     */
    public function testGetPath()
    {

        $ex = new FileNotFoundException( 'c:\\Temp\\My Folder\\äöü.txt' );

        $this->assertSame( 'c:\\Temp\\My Folder\\äöü.txt', $ex->getPath() );
        // The file is used "as is" in the message too:
        $this->assertSame( 'There was a error with path [c:\\Temp\\My Folder\\äöü.txt]! File not exists.', $ex->getMessage() );

        // Calling `getPath()` again must be without side effects:
        $this->assertSame( 'c:\\Temp\\My Folder\\äöü.txt', $ex->getPath() );

        // A blank file parameter is allowed and stays blank:
        $ex = new FileNotFoundException( ' ' );
        $this->assertSame( ' ', $ex->getPath() );
        $this->assertSame( 'There was a error with path [ ]! File not exists.', $ex->getMessage() );

        // An empty file parameter is allowed and stays empty:
        $ex = new FileNotFoundException( '' );
        $this->assertSame( '', $ex->getPath() );
        $this->assertSame( 'There was a error with path []! File not exists.', $ex->getMessage() );

        // A relative file is neither checked nor "expanded" in any way:
        $ex = new FileNotFoundException( './does/not/exist.txt' );
        $this->assertSame( './does/not/exist.txt', $ex->getPath() );

    }

    /**
     * Test the `$message` parameter, which is appended to the own fixed text.
     * `NiirrtyException::appendMessage()` decides with `empty()` whether
     * anything is appended.
     *
     * @since v0.1.0
     */
    public function testConstructorMessage()
    {

        // NULL appendix behaves like no appendix at all:
        $ex = new FileNotFoundException( '/f.txt', null );
        $this->assertSame( 'There was a error with path [/f.txt]! File not exists.', $ex->getMessage() );

        // An empty appendix behaves like no appendix:
        $ex = new FileNotFoundException( '/f.txt', '' );
        $this->assertSame( 'There was a error with path [/f.txt]! File not exists.', $ex->getMessage() );

        // A real appendix is appended behind the own fixed text, separated by
        // a single space:
        $ex = new FileNotFoundException( '/f.txt', 'created by other process' );
        $this->assertSame( 'There was a error with path [/f.txt]! File not exists. created by other process', $ex->getMessage() );

        // A blank appendix is appended also, so the message ends with 2 spaces:
        $ex = new FileNotFoundException( '/f.txt', ' ' );
        $this->assertSame( 'There was a error with path [/f.txt]! File not exists.  ', $ex->getMessage() );

        // Findings: a "0" appendix is lost because `appendMessage()` tests the
        // appendix with `empty()` - but the own fixed text is NEVER lost:
        $ex = new FileNotFoundException( '/f.txt', '0' );
        $this->assertSame( 'There was a error with path [/f.txt]! File not exists.', $ex->getMessage() );

        // The own message text is not configurable: a `$message` only ever adds
        // something, the text 'File not exists.' is always there:
        $this->assertStringContainsString( 'File not exists.', $ex->getMessage() );
        $this->assertStringStartsWith( 'There was a error with path [/f.txt]!', $ex->getMessage() );

    }

    /**
     * Test the `$code` parameter with `int` values: the code must be forwarded
     * to the inner `\Exception` and must not influence file or message.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeInt()
    {

        $ex = new FileNotFoundException( '/f.txt', null, 4711 );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f.txt]! File not exists.', $ex->getMessage() );

        $ex = new FileNotFoundException( '/f.txt', null, 0 );
        $this->assertSame( 0, $ex->getCode() );

        $ex = new FileNotFoundException( '/f.txt', null, \E_USER_ERROR );
        $this->assertSame( 256, $ex->getCode() );

        $ex = new FileNotFoundException( '/f.txt', null, \E_USER_WARNING );
        $this->assertSame( 512, $ex->getCode() );

        $ex = new FileNotFoundException( '/other/file.txt', 'appendix', 512 );
        $this->assertSame( 512, $ex->getCode() );
        $this->assertSame( '/other/file.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/other/file.txt]! File not exists. appendix', $ex->getMessage() );

    }

    /**
     * Test the `$code` parameter with an alphanumeric string: the signature
     * documents `string|int`, so the call itself is valid, but the inner
     * `\Exception` constructor rejects the code with a `TypeError`.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Exception::__construct(): Argument #2 ($code) must be of type int, string given' );

        new FileNotFoundException( '/f.txt', 'appendix', 'MY-CODE' );

    }

    /**
     * Test the `$code` parameter with a numeric string: also these are rejected
     * by the inner `\Exception` constructor (findings see the journal!).
     *
     * @since v0.1.0
     */
    public function testConstructorCodeNumericString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Exception::__construct(): Argument #2 ($code) must be of type int, string given' );

        new FileNotFoundException( '/f.txt', 'appendix', '4711' );

    }

    /**
     * Test the `$code` parameter with a `float`: the parameter type `string|int`
     * rejects it directly at the constructor call, so a `TypeError` is thrown
     * and the inner `\Exception` constructor is never entered (findings see the
     * journal!).
     *
     * @since v0.1.0
     */
    public function testConstructorCodeFloat()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #3 ($code) must be of type string|int, float given' );

        new FileNotFoundException( '/f.txt', 'appendix', 256.5 );

    }

    /**
     * Test the `$file` parameter with `NULL`: the parameter is declared `string`
     * without default so `NULL` is rejected with a `TypeError` (strict typing).
     *
     * @since v0.1.0
     */
    public function testConstructorFileNull()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #1 ($file) must be of type string, null given' );

        new FileNotFoundException( null );

    }

    /**
     * Test the `$file` parameter with an `int`: the parameter is declared
     * `string` without default value - in contrast to the implicitly nullable
     * `$message` parameter of e.g. `Niirrty\IO\FileAccessException::Read()` - so
     * an `int` is rejected with a `TypeError` (strict typing).
     *
     * @since v0.1.0
     */
    public function testConstructorFileInt()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #1 ($file) must be of type string, int given' );

        new FileNotFoundException( 4711 );

    }

    /**
     * Test the `$file` parameter with an array: the `TypeError` of the parameter
     * type declaration is thrown - the message text differs from a scalar one.
     *
     * @since v0.1.0
     */
    public function testConstructorFileArray()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #1 ($file) must be of type string, array given' );

        new FileNotFoundException( [1, 2] );

    }

    /**
     * Test the `$message` parameter with a not compatible type: it is declared
     * `?string` so an `int` is rejected with a `TypeError` (strict typing).
     *
     * @since v0.1.0
     */
    public function testConstructorMessageWrongType()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #2 ($message) must be of type ?string, int given' );

        new FileNotFoundException( '/f.txt', 4711 );

    }

    /**
     * Test the `$message` parameter with a numeric string: a string is accepted
     * as appendix, also a numeric one (in contrast to the `$code` parameter!).
     *
     * @since v0.1.0
     */
    public function testConstructorMessageNumericString()
    {

        $ex = new FileNotFoundException( '/f.txt', '4711' );

        $this->assertSame( 'There was a error with path [/f.txt]! File not exists. 4711', $ex->getMessage() );
        // The appendix never changes the code:
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( '/f.txt', $ex->getPath() );

    }

    /**
     * Test the `$previous` parameter: any `\Throwable` may be used, and the
     * previous exception has nothing to do with own file, message or code.
     *
     * @since v0.1.0
     */
    public function testConstructorPrevious()
    {

        // Without previous exception:
        $ex = new FileNotFoundException( '/f.txt' );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // A plain `\Exception` may be used as previous exception:
        $prev = new \Exception( 'prev msg' );
        $ex = new FileNotFoundException( '/f.txt', 'appendix', 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 'prev msg', $ex->getPrevious()->getMessage() );

        // A `NiirrtyException` may be used as previous exception too:
        $prev = new NiirrtyException( 'prev Niirrty msg', 512 );
        $ex = new FileNotFoundException( '/f.txt', 'appendix', 4711, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( NiirrtyException::class, $ex->getPrevious() );
        $this->assertNotInstanceOf( IOException::class, $ex->getPrevious() );
        $this->assertSame( 'prev Niirrty msg', $ex->getPrevious()->getMessage() );
        $this->assertSame( 512, $ex->getPrevious()->getCode() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f.txt]! File not exists. appendix', $ex->getMessage() );

        // Another `FileNotFoundException` may be the previous exception:
        $prev = new FileNotFoundException( '/prev/file.txt' );
        $ex = new FileNotFoundException( '/f.txt', 'nested', 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( FileNotFoundException::class, $ex->getPrevious() );
        $this->assertSame( '/prev/file.txt', $ex->getPrevious()->getPath() );
        $this->assertSame( '/f.txt', $ex->getPath() );
        $this->assertFalse( $ex->getPrevious()->hasPrevious() );
        $this->assertSame( 'There was a error with path [/prev/file.txt]! File not exists.', $ex->getPrevious()->getMessage() );

        // A sibling exception class may be the previous exception too:
        $ex = new FileNotFoundException( '/f.txt', null, 256, new FileAlreadyExistsException( '/prev/exists.txt' ) );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( FileAlreadyExistsException::class, $ex->getPrevious() );
        $this->assertNotInstanceOf( FileNotFoundException::class, $ex->getPrevious() );
        $this->assertSame( '/prev/exists.txt', $ex->getPrevious()->getPath() );

        // Any other `\Throwable` is accepted as previous exception:
        $ex = new FileNotFoundException( '/f.txt', null, 256, new \RuntimeException( 'runtime' ) );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( \RuntimeException::class, $ex->getPrevious() );

    }

    /**
     * Test the `$previous` parameter with a value, that is no `\Throwable`: the
     * parameter type `?\Throwable` rejects it directly at the constructor call.
     *
     * @since v0.1.0
     */
    public function testConstructorPreviousWrongType()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #4 ($previous) must be of type ?Throwable, string given' );

        new FileNotFoundException( '/f.txt', 'msg', 256, 'no throwable' );

    }

    /**
     * Test a `FileNotFoundException` in a `try`/`catch` block: neither the
     * file, the message nor the code may change when throwing. Because it
     * extends from `IOException` and `NiirrtyException` it may be catched as
     * one of them too.
     *
     * @since v0.1.0
     */
    public function testThrowAndCatch()
    {

        try
        {
            throw new FileNotFoundException( '/probe/missing.txt', 'file is not there' );
        }
        catch ( FileNotFoundException $ex )
        {
            $this->assertSame( '/probe/missing.txt', $ex->getPath() );
            $this->assertSame( 'There was a error with path [/probe/missing.txt]! File not exists. file is not there', $ex->getMessage() );
            $this->assertSame( 256, $ex->getCode() );
        }

        // Because it extends from `IOException` it may also be catched as one:
        try
        {
            throw new FileNotFoundException( '/probe/missing.txt' );
        }
        catch ( IOException $ex )
        {
            $this->assertInstanceOf( FileNotFoundException::class, $ex );
            $this->assertSame( '/probe/missing.txt', $ex->getPath() );
            $this->assertSame( 'There was a error with path [/probe/missing.txt]! File not exists.', $ex->getMessage() );
        }

        // And as a `NiirrtyException`:
        try
        {
            throw new FileNotFoundException( '/probe/missing.txt' );
        }
        catch ( NiirrtyException $ex )
        {
            $this->assertInstanceOf( FileNotFoundException::class, $ex );
            $this->assertTrue( $ex instanceof \Exception );
        }

        // A sibling class does NOT catch a `FileNotFoundException`:
        try
        {
            try
            {
                throw new FileNotFoundException( '/probe/missing.txt' );
            }
            catch ( FileAlreadyExistsException $ex )
            {
                $this->assertTrue( false );
            }
        }
        catch ( FileNotFoundException $ex )
        {
            $this->assertSame( '/probe/missing.txt', $ex->getPath() );
        }

    }

    /**
     * Test the inherited `getErrorMessage()` method for all error levels, that
     * `NiirrtyException::GetCodeName()` handles before the deprecated constant
     * `E_STRICT` is evaluated.
     *
     * @since v0.1.0
     */
    public function testInheritedGetErrorMessage()
    {

        $ex = new FileNotFoundException( '/probe/file.txt' );
        $this->assertSame( 'ERROR(256): There was a error with path [/probe/file.txt]! File not exists.', $ex->getErrorMessage() );

        $ex = new FileNotFoundException( '/f.txt', null, \E_USER_WARNING );
        $this->assertSame( 'WARNING(512): There was a error with path [/f.txt]! File not exists.', $ex->getErrorMessage() );

        $ex = new FileNotFoundException( '/f.txt', null, \E_USER_DEPRECATED );
        $this->assertSame( 'DEPRECATED(16384): There was a error with path [/f.txt]! File not exists.', $ex->getErrorMessage() );

        $ex = new FileNotFoundException( '/f.txt', null, \E_USER_NOTICE );
        $this->assertSame( 'NOTICE(1024): There was a error with path [/f.txt]! File not exists.', $ex->getErrorMessage() );

        // The appendix is contained in the error message too:
        $ex = new FileNotFoundException( '/f.txt', 'appendix', \E_USER_WARNING );
        $this->assertSame( 'WARNING(512): There was a error with path [/f.txt]! File not exists. appendix', $ex->getErrorMessage() );

    }

    /**
     * Test the inherited `getErrorMessage()` method for a code, that is NOT one
     * of the known PHP error levels (findings see `journal/01-ioexception.md`):
     * `NiirrtyException::GetCodeName()` internally evaluates the deprecated
     * constant `E_STRICT`, what Niirrty.Core's global error handler turns into a
     * thrown `Niirrty\PhpException`!
     *
     * @since v0.1.0
     */
    public function testGetErrorMessageNotUsable()
    {

        $ex = new FileNotFoundException( '/probe/file.txt', 'appendix', 4711 );

        $this->expectException( PhpException::class );
        $this->expectExceptionMessage( 'Constant E_STRICT is deprecated since 8.4, the error level was removed' );

        $ex->getErrorMessage();

    }

    /**
     * Test the inherited `getErrorMessage()` method with a previous exception:
     * the `$appendPreviousByNewline` parameter decides the separator, and the
     * previous exception is appended behind the own message. The previous
     * exception's code must also be a usable error level!
     *
     * @since v0.1.0
     */
    public function testGetErrorMessageWithPrevious()
    {

        $prev = new FileNotFoundException( '/prev/file.txt' );
        $ex = new FileNotFoundException( '/f.txt', 'nested', 256, $prev );

        // Default: separator is a single space:
        $this->assertSame( 'ERROR(256): There was a error with path [/f.txt]! File not exists. nested ERROR(256): There was a error with path [/prev/file.txt]! File not exists.', $ex->getErrorMessage() );

        // With new line separator:
        $expected = 'ERROR(256): There was a error with path [/f.txt]! File not exists. nested' . "\n" . 'ERROR(256): There was a error with path [/prev/file.txt]! File not exists.';
        $this->assertSame( $expected, $ex->getErrorMessage( true ) );

        // A non-Niirrty previous exception is included by its message only:
        $ex = new FileNotFoundException( '/f.txt', null, 256, new \Exception( 'plain previous' ) );
        $this->assertSame( 'ERROR(256): There was a error with path [/f.txt]! File not exists. plain previous', $ex->getErrorMessage() );

    }

    /**
     * Test the inherited `__toString()` method: casting the exception to a
     * string must return the `toCustomString()` result, which contains the
     * fully qualified class name, the location and the message - but NOT the
     * code name of `getErrorMessage()`.
     *
     * @since v0.1.0
     */
    public function testToString()
    {

        $ex = new FileNotFoundException( '/probe/file.txt', 'appendix' );

        $str = (string) $ex;

        $this->assertStringContainsString( 'Niirrty\\IO\\FileNotFoundException', $str );
        $this->assertStringContainsString( 'There was a error with path [/probe/file.txt]! File not exists. appendix', $str );
        $this->assertStringNotContainsString( 'ERROR(256)', $str );

        // An own `__toString()` would be required to change that: the message
        // alone is identical to `getMessage()` of the instance.
        $this->assertStringContainsString( $ex->getMessage(), $str );

    }

    /**
     * Test the class structure and API surface by reflection: the class extends
     * from `Niirrty\IO\IOException`, declares no own constant and no own
     * property, its only own method is `__construct()` with the four documented
     * parameters and `getPath()` is just inherited from `IOException`.
     *
     * @since v0.1.0
     */
    public function testReflection()
    {

        $rc = new \ReflectionClass( FileNotFoundException::class );

        $this->assertSame( 'Niirrty\\IO\\FileNotFoundException', $rc->getName() );
        $this->assertSame( 'Niirrty\\IO\\IOException', $rc->getParentClass()->getName() );
        $this->assertFalse( $rc->isAbstract() );
        $this->assertSame( [], $rc->getConstants() );
        $this->assertSame( [], $rc->getStaticProperties() );
        $this->assertStringEndsWith( '/src/FileNotFoundException.php', $rc->getFileName() );

        $rm = new \ReflectionMethod( FileNotFoundException::class, '__construct' );
        $ps = $rm->getParameters();

        $this->assertSame( 4, \count( $ps ) );
        $this->assertSame( 'file', $ps[0]->getName() );
        $this->assertSame( 'message', $ps[1]->getName() );
        $this->assertSame( 'code', $ps[2]->getName() );
        $this->assertSame( 'previous', $ps[3]->getName() );

        $this->assertFalse( $ps[0]->isOptional() );
        $this->assertTrue( $ps[1]->isOptional() );
        $this->assertTrue( $ps[2]->isOptional() );
        $this->assertTrue( $ps[3]->isOptional() );

        $this->assertSame( 4, $rm->getNumberOfParameters() );
        $this->assertSame( 1, $rm->getNumberOfRequiredParameters() );
        $this->assertFalse( $rm->isVariadic() );
        $this->assertFalse( $rm->isStatic() );
        $this->assertFalse( $rm->isPrivate() );
        $this->assertTrue( $rm->isPublic() );
        $this->assertFalse( $rm->isFinal() );
        $this->assertStringEndsWith( '/src/FileNotFoundException.php', $rm->getFileName() );

        // The `$code` parameter defaults to the constant `\E_USER_ERROR`:
        $this->assertSame( 256, $ps[2]->getDefaultValue() );
        $this->assertNull( $ps[1]->getDefaultValue() );
        $this->assertNull( $ps[3]->getDefaultValue() );

        // In contrast to the promoted `$path` of `Niirrty\IO\IOException` the
        // `$file` parameter is NO promoted property:
        $this->assertFalse( $ps[0]->isPromoted() );

        // `getPath()` is inherited from `Niirrty\IO\IOException`:
        $pm = new \ReflectionMethod( FileNotFoundException::class, 'getPath' );
        $this->assertStringEndsWith( '/src/IOException.php', $pm->getFileName() );
        $this->assertTrue( $pm->isFinal() );
        $this->assertSame( 0, $pm->getNumberOfParameters() );

    }

    #endregion


}

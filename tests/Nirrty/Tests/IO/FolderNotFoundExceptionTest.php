<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license    MIT
 * @package    Niirrty\Tests\IO
 * @since      2026-11-16
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
 * Test case for the class `Niirrty\IO\FolderNotFoundException`.
 *
 * The source in `src/` is NOT changed, the tests assert the CURRENT behaviour
 * (see the "Findings" section of `journal/07-folder-not-found-exception.md`):
 * the class is the folder counterpart of `Niirrty\IO\FileNotFoundException`
 * and provides its own, fixed `IOException` text 'The Folder not exists.'.
 * All optional parameters are explicitly nullable (`?string $message`,
 * `?\Throwable $previous`) so - in contrast to e.g. `Niirrty\IO\FileAccessException` -
 * the FIRST use of the class definition does NOT raise a PHP 8.5 deprecation
 * notice and therefore no `Niirrty\PhpException`. Nevertheless this test stays
 * the first test method to document that. The class declares no own method
 * except the constructor, has no constants, no own property and no factory
 * method, in contrast to `Niirrty\IO\FolderAccessException`.
 *
 * @since v0.1.0
 */
class FolderNotFoundExceptionTest extends TestCase
{


    #region // = = =   T E S T   M E T H O D S  = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Test the FIRST use of the class definition: different to
     * `Niirrty\IO\FileAccessException` all optional parameters of this class
     * use the explicit nullable form (`?string $message`, `?\Throwable $previous`)
     * so the class definition causes no PHP 8.5 "implicitly nullable parameter"
     * deprecation and the class is usable the ordinary way immediately.
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

        $ex = new FolderNotFoundException( '/probe/first' );

        $this->assertInstanceOf( FolderNotFoundException::class, $ex );
        $this->assertSame( '/probe/first', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/probe/first]! The Folder not exists.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( \E_USER_ERROR, $ex->getCode() );

    }

    /**
     * Test the class hierarchy: a `FolderNotFoundException` is an
     * `Niirrty\IO\IOException`, a `Niirrty\NiirrtyException`, an `\Exception`
     * and a `\Throwable`, but the parent classes are NOT
     * `FolderNotFoundException`s.
     *
     * @since v0.1.0
     */
    public function testClassHierarchy()
    {

        $ex = new FolderNotFoundException( '/probe/folder' );

        // instanceof chain to the base classes:
        $this->assertInstanceOf( FolderNotFoundException::class, $ex );
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( NiirrtyException::class, $ex );
        $this->assertInstanceOf( \Exception::class, $ex );
        $this->assertTrue( $ex instanceof \Throwable );
        // A special IO exception class of this package also is throwable.
        $this->assertTrue( $ex instanceof FolderNotFoundException );

        // Direct and indirect parent classes:
        $this->assertSame( 'Niirrty\IO\IOException', (string) \get_parent_class( $ex ) );
        $this->assertSame( 'Niirrty\NiirrtyException', (string) \get_parent_class( new IOException( '/probe/folder' ) ) );
        $this->assertSame( 'Niirrty\IO\FolderNotFoundException', (string) \get_class( $ex ) );

        // An ordinary `IOException` is NO `FolderNotFoundException`:
        $ioex = new IOException( '/probe/folder' );
        $this->assertNotInstanceOf( FolderNotFoundException::class, $ioex );
        $this->assertSame( 'There was a error with path [/probe/folder]!', $ioex->getMessage() );

        // A `NiirrtyException` also is no `FolderNotFoundException`:
        $nex = new NiirrtyException( 'some error', 512 );
        $this->assertNotInstanceOf( FolderNotFoundException::class, $nex );
        $this->assertNotInstanceOf( IOException::class, $nex );

    }

    /**
     * Test the (complete) instanceof chain incl. the sibling exception classes
     * of the `Niirrty\IO` package: a `FolderNotFoundException` is NONE of them,
     * because every one of them provides its own, fixed message text.
     *
     * @since v0.1.0
     */
    public function testInstanceofChain()
    {

        $ex = new FolderNotFoundException( '/probe/folder' );

        $this->assertInstanceOf( FolderNotFoundException::class, $ex );
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( NiirrtyException::class, $ex );
        $this->assertInstanceOf( \Exception::class, $ex );
        $this->assertTrue( is_object( $ex ) );
        $this->assertTrue( $ex instanceof \Throwable );

        // All sibling exception classes of the `Niirrty\IO` package:
        $this->assertNotInstanceOf( FileAlreadyExistsException::class, $ex );
        $this->assertNotInstanceOf( FileFormatException::class, $ex );
        $this->assertNotInstanceOf( FileNotFoundException::class, $ex );
        $this->assertNotInstanceOf( FolderAccessException::class, $ex );

    }

    /**
     * Test, that a `FolderNotFoundException` is NOT one of the sibling classes
     * and a sibling class is NOT a `FolderNotFoundException`, even when the
     * inner `Niirrty\IO\IOException` (and therefore the `getPath()` behaviour)
     * is equal - especially compared with the "file" counterpart.
     *
     * @since v0.1.0
     */
    public function testNotInstanceOfSiblings()
    {

        $ex = new FolderNotFoundException( '/f' );
        $sibling = new FileNotFoundException( '/f' );

        $this->assertNotInstanceOf( FileNotFoundException::class, $ex );
        $this->assertNotInstanceOf( FolderNotFoundException::class, $sibling );

        // Both ARE `IOException`s, but they keep their own fixed message text:
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( IOException::class, $sibling );
        $this->assertSame( 'There was a error with path [/f]! The Folder not exists.', $ex->getMessage() );
        $this->assertSame( 'There was a error with path [/f]! File not exists.', $sibling->getMessage() );

        // Both share the same, inherited `getPath()` behaviour:
        $this->assertSame( '/f', $ex->getPath() );
        $this->assertSame( '/f', $sibling->getPath() );

        // The folder sibling `FolderAccessException` neither:
        $other = new FolderAccessException( '/f' );
        $this->assertNotInstanceOf( FolderAccessException::class, $ex );
        $this->assertNotInstanceOf( FolderNotFoundException::class, $other );
        $this->assertSame( 'There was a error with path [/f]! Could not read folder.', $other->getMessage() );

    }

    /**
     * Test the constructor with the folder parameter only: the folder must be
     * kept as path (inherited `getPath()`), the own fixed text 'The Folder not
     * exists.' must be appended to the `IOException` message - handed over as
     * `$message` parameter to the parent constructor - and the code must
     * default to the constant `\E_USER_ERROR` (256).
     *
     * @since v0.1.0
     */
    public function testConstructorDefaults()
    {

        $ex = new FolderNotFoundException( '/probe/folder' );

        $this->assertSame( '/probe/folder', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/probe/folder]! The Folder not exists.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( \E_USER_ERROR, $ex->getCode() );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // Each call returns a new, independent instance:
        $ex2 = new FolderNotFoundException( '/probe/folder' );
        $this->assertNotEquals( $ex, $ex2 );
        $this->assertNotSame( $ex, $ex2 );

    }

    /**
     * Test the `$folder` parameter and the inherited `getPath()`: the folder
     * must always be returned "as is", also one with spaces, umlauts and
     * backslashes, a blank and an empty folder are accepted.
     *
     * @since v0.1.0
     */
    public function testGetPath()
    {

        $ex = new FolderNotFoundException( 'c:\\Temp\\My Folder\\äöü' );

        $this->assertSame( 'c:\\Temp\\My Folder\\äöü', $ex->getPath() );
        // The folder is used "as is" in the message too:
        $this->assertSame( 'There was a error with path [c:\\Temp\\My Folder\\äöü]! The Folder not exists.', $ex->getMessage() );

        // Calling `getPath()` again must be without side effects:
        $this->assertSame( 'c:\\Temp\\My Folder\\äöü', $ex->getPath() );

        // A blank folder parameter is allowed and stays blank:
        $ex = new FolderNotFoundException( ' ' );
        $this->assertSame( ' ', $ex->getPath() );
        $this->assertSame( 'There was a error with path [ ]! The Folder not exists.', $ex->getMessage() );

        // An empty folder parameter is allowed and stays empty:
        $ex = new FolderNotFoundException( '' );
        $this->assertSame( '', $ex->getPath() );
        $this->assertSame( 'There was a error with path []! The Folder not exists.', $ex->getMessage() );

        // A relative folder is neither checked nor "expanded" in any way:
        $ex = new FolderNotFoundException( './does/not/exist' );
        $this->assertSame( './does/not/exist', $ex->getPath() );

        // Findings: the class is called "NotFoundException" but the source
        // NEVER touches the filesystem - an existing folder may be passed too:
        $ex = new FolderNotFoundException( '/' );
        $this->assertSame( '/', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/]! The Folder not exists.', $ex->getMessage() );

    }

    /**
     * Test the `$folder` parameter with `NULL`: the parameter is declared
     * `string` without default so `NULL` is rejected with a `TypeError`
     * (strict typing).
     *
     * @since v0.1.0
     */
    public function testConstructorFolderNull()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #1 ($folder) must be of type string, null given' );

        new FolderNotFoundException( null );

    }

    /**
     * Test the `$folder` parameter with an `int`: the parameter is declared
     * `string` without default value - in contrast to the implicitly nullable
     * `$message` parameter of e.g. `Niirrty\IO\FileAccessException::Read()` - so
     * an `int` is rejected with a `TypeError` (strict typing).
     *
     * @since v0.1.0
     */
    public function testConstructorFolderInt()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #1 ($folder) must be of type string, int given' );

        new FolderNotFoundException( 4711 );

    }

    /**
     * Test the `$folder` parameter with an array: the `TypeError` of the
     * parameter type declaration is thrown - the message text differs from a
     * scalar one.
     *
     * @since v0.1.0
     */
    public function testConstructorFolderArray()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #1 ($folder) must be of type string, array given' );

        new FolderNotFoundException( [1, 2] );

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
        $ex = new FolderNotFoundException( '/f', null );
        $this->assertSame( 'There was a error with path [/f]! The Folder not exists.', $ex->getMessage() );

        // An empty appendix behaves like no appendix:
        $ex = new FolderNotFoundException( '/f', '' );
        $this->assertSame( 'There was a error with path [/f]! The Folder not exists.', $ex->getMessage() );

        // A real appendix is appended behind the own fixed text, separated by
        // a single space:
        $ex = new FolderNotFoundException( '/f', 'created by other process' );
        $this->assertSame( 'There was a error with path [/f]! The Folder not exists. created by other process', $ex->getMessage() );

        // A blank appendix is appended also, so the message ends with 2 spaces:
        $ex = new FolderNotFoundException( '/f', ' ' );
        $this->assertSame( 'There was a error with path [/f]! The Folder not exists.  ', $ex->getMessage() );

        // Findings: a "0" appendix is lost because `appendMessage()` tests the
        // appendix with `empty()` - but the own fixed text is NEVER lost:
        $ex = new FolderNotFoundException( '/f', '0' );
        $this->assertSame( 'There was a error with path [/f]! The Folder not exists.', $ex->getMessage() );

        // The own message text is not configurable: a `$message` only ever adds
        // something, the text 'The Folder not exists.' is always there:
        $this->assertStringContainsString( 'The Folder not exists.', $ex->getMessage() );
        $this->assertStringStartsWith( 'There was a error with path [/f]!', $ex->getMessage() );

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

        new FolderNotFoundException( '/f', 4711 );

    }

    /**
     * Test the `$message` parameter with a numeric string: a string is accepted
     * as appendix, also a numeric one (in contrast to the `$code` parameter!).
     *
     * @since v0.1.0
     */
    public function testConstructorMessageNumericString()
    {

        $ex = new FolderNotFoundException( '/f', '4711' );

        $this->assertSame( 'There was a error with path [/f]! The Folder not exists. 4711', $ex->getMessage() );
        // The appendix never changes the code:
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( '/f', $ex->getPath() );

    }

    /**
     * Test the `$code` parameter with `int` values: the code must be forwarded
     * to the inner `\Exception` and must not influence folder or message.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeInt()
    {

        $ex = new FolderNotFoundException( '/f', null, 4711 );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f]! The Folder not exists.', $ex->getMessage() );

        $ex = new FolderNotFoundException( '/f', null, 0 );
        $this->assertSame( 0, $ex->getCode() );

        $ex = new FolderNotFoundException( '/f', null, \E_USER_ERROR );
        $this->assertSame( 256, $ex->getCode() );

        $ex = new FolderNotFoundException( '/f', null, \E_USER_WARNING );
        $this->assertSame( 512, $ex->getCode() );

        $ex = new FolderNotFoundException( '/f', null, -1 );
        $this->assertSame( -1, $ex->getCode() );

        $ex = new FolderNotFoundException( '/other/folder', 'appendix', 512 );
        $this->assertSame( 512, $ex->getCode() );
        $this->assertSame( '/other/folder', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/other/folder]! The Folder not exists. appendix', $ex->getMessage() );

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

        new FolderNotFoundException( '/f', 'appendix', 'MY-CODE' );

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

        new FolderNotFoundException( '/f', 'appendix', '4711' );

    }

    /**
     * Test the `$code` parameter with a `float`: the parameter type `string|int`
     * rejects it directly at the constructor call, so a `TypeError` is thrown
     * and the inner `\Exception` constructor is never entered.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeFloat()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #3 ($code) must be of type string|int, float given' );

        new FolderNotFoundException( '/f', 'appendix', 256.5 );

    }

    /**
     * Test the `$code` parameter with `NULL`: the parameter has a default
     * value, but its type is the not nullable union `string|int`, so `NULL` is
     * rejected with a `TypeError`.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeNull()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #3 ($code) must be of type string|int, null given' );

        new FolderNotFoundException( '/f', 'appendix', null );

    }

    /**
     * Test the `$previous` parameter: any `\Throwable` may be used, and the
     * previous exception has nothing to do with own folder, message or code.
     *
     * @since v0.1.0
     */
    public function testConstructorPrevious()
    {

        // Without previous exception:
        $ex = new FolderNotFoundException( '/f' );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // Explicit NULL behaves like no previous exception at all:
        $ex = new FolderNotFoundException( '/f', 'appendix', 256, null );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // A plain `\Exception` may be used as previous exception:
        $prev = new \Exception( 'prev msg' );
        $ex = new FolderNotFoundException( '/f', 'appendix', 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 'prev msg', $ex->getPrevious()->getMessage() );

        // A `NiirrtyException` may be used as previous exception too:
        $prev = new NiirrtyException( 'prev Niirrty msg', 512 );
        $ex = new FolderNotFoundException( '/f', 'appendix', 4711, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( NiirrtyException::class, $ex->getPrevious() );
        $this->assertNotInstanceOf( IOException::class, $ex->getPrevious() );
        $this->assertSame( 'prev Niirrty msg', $ex->getPrevious()->getMessage() );
        $this->assertSame( 512, $ex->getPrevious()->getCode() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f]! The Folder not exists. appendix', $ex->getMessage() );

        // Another `FolderNotFoundException` may be the previous exception:
        $prev = new FolderNotFoundException( '/prev/folder' );
        $ex = new FolderNotFoundException( '/f', 'nested', 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( FolderNotFoundException::class, $ex->getPrevious() );
        $this->assertSame( '/prev/folder', $ex->getPrevious()->getPath() );
        $this->assertSame( '/f', $ex->getPath() );
        $this->assertFalse( $ex->getPrevious()->hasPrevious() );
        $this->assertSame( 'There was a error with path [/prev/folder]! The Folder not exists.', $ex->getPrevious()->getMessage() );

        // A sibling exception class may be the previous exception too:
        $ex = new FolderNotFoundException( '/f', null, 256, new FileNotFoundException( '/prev/missing.txt' ) );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( FileNotFoundException::class, $ex->getPrevious() );
        $this->assertNotInstanceOf( FolderNotFoundException::class, $ex->getPrevious() );
        $this->assertSame( '/prev/missing.txt', $ex->getPrevious()->getPath() );

        // Any other `\Throwable` is accepted as previous exception:
        $ex = new FolderNotFoundException( '/f', null, 256, new \RuntimeException( 'runtime' ) );
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

        new FolderNotFoundException( '/f', 'msg', 256, 'no throwable' );

    }

    /**
     * Test the constructor as well as named parameters (PHP 8+): the parameter
     * names are `folder`, `message`, `code` and `previous`.
     *
     * @since v0.1.0
     */
    public function testNamedParameters()
    {

        $ex = new FolderNotFoundException( folder: '/probe/named', message: 'named appendix', code: 4711 );
        $this->assertSame( 'There was a error with path [/probe/named]! The Folder not exists. named appendix', $ex->getMessage() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( '/probe/named', $ex->getPath() );

        // The order of named parameters does not matter:
        $ex = new FolderNotFoundException( code: 4712, message: 'second', folder: '/probe/other' );
        $this->assertSame( 'There was a error with path [/probe/other]! The Folder not exists. second', $ex->getMessage() );
        $this->assertSame( 4712, $ex->getCode() );

        // The `$previous` parameter may be named too:
        $ex = new FolderNotFoundException( '/f', previous: new \Exception( 'prev' ) );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( 'prev', $ex->getPrevious()->getMessage() );
        $this->assertSame( 256, $ex->getCode() );

        // The parameter name `folder` is NOT `path` (as the promoted property
        // of `Niirrty\IO\IOException`) and NOT `file` (as of the counterpart
        // `Niirrty\IO\FileNotFoundException`):
        $this->expectException( \Error::class );
        $this->expectExceptionMessage( 'Unknown named parameter $file' );

        new FolderNotFoundException( file: '/probe/f.txt' );

    }

    /**
     * Test a `FolderNotFoundException` in a `try`/`catch` block: neither the
     * folder, the message nor the code may change when throwing. Because it
     * extends from `IOException` and `NiirrtyException` it may be catched as
     * one of them too.
     *
     * @since v0.1.0
     */
    public function testThrowAndCatch()
    {

        try
        {
            throw new FolderNotFoundException( '/probe/missing', 'folder is not there' );
        }
        catch ( FolderNotFoundException $ex )
        {
            $this->assertSame( '/probe/missing', $ex->getPath() );
            $this->assertSame( 'There was a error with path [/probe/missing]! The Folder not exists. folder is not there', $ex->getMessage() );
            $this->assertSame( 256, $ex->getCode() );
        }

        // Because it extends from `IOException` it may also be catched as one:
        try
        {
            throw new FolderNotFoundException( '/probe/missing' );
        }
        catch ( IOException $ex )
        {
            $this->assertInstanceOf( FolderNotFoundException::class, $ex );
            $this->assertSame( '/probe/missing', $ex->getPath() );
            $this->assertSame( 'There was a error with path [/probe/missing]! The Folder not exists.', $ex->getMessage() );
        }

        // And as a `NiirrtyException`:
        try
        {
            throw new FolderNotFoundException( '/probe/missing' );
        }
        catch ( NiirrtyException $ex )
        {
            $this->assertInstanceOf( FolderNotFoundException::class, $ex );
            $this->assertTrue( $ex instanceof \Exception );
        }

        // A sibling class does NOT catch a `FolderNotFoundException`:
        try
        {
            try
            {
                throw new FolderNotFoundException( '/probe/missing' );
            }
            catch ( FileNotFoundException $ex )
            {
                $this->assertTrue( false );
            }
        }
        catch ( FolderNotFoundException $ex )
        {
            $this->assertSame( '/probe/missing', $ex->getPath() );
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

        $ex = new FolderNotFoundException( '/probe/folder' );
        $this->assertSame( 'ERROR(256): There was a error with path [/probe/folder]! The Folder not exists.', $ex->getErrorMessage() );

        $ex = new FolderNotFoundException( '/f', null, \E_USER_WARNING );
        $this->assertSame( 'WARNING(512): There was a error with path [/f]! The Folder not exists.', $ex->getErrorMessage() );

        $ex = new FolderNotFoundException( '/f', null, \E_USER_DEPRECATED );
        $this->assertSame( 'DEPRECATED(16384): There was a error with path [/f]! The Folder not exists.', $ex->getErrorMessage() );

        $ex = new FolderNotFoundException( '/f', null, \E_USER_NOTICE );
        $this->assertSame( 'NOTICE(1024): There was a error with path [/f]! The Folder not exists.', $ex->getErrorMessage() );

        // Also the "system" error levels work (not only the `E_USER_*` ones):
        $ex = new FolderNotFoundException( '/f', null, \E_WARNING );
        $this->assertSame( 'WARNING(2): There was a error with path [/f]! The Folder not exists.', $ex->getErrorMessage() );

        // The appendix is contained in the error message too:
        $ex = new FolderNotFoundException( '/f', 'appendix', \E_USER_WARNING );
        $this->assertSame( 'WARNING(512): There was a error with path [/f]! The Folder not exists. appendix', $ex->getErrorMessage() );

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

        $ex = new FolderNotFoundException( '/probe/folder', 'appendix', 4711 );

        $this->assertSame( 4711, $ex->getCode() );

        $this->expectException( PhpException::class );
        $this->expectExceptionMessage( 'Constant E_STRICT is deprecated since 8.4, the error level was removed' );

        $ex->getErrorMessage();

    }

    /**
     * Test the inherited `getErrorMessage()` method with the error code `0`,
     * the value a plain `\Exception` gets when the code is omitted: also this
     * is no PHP error level so `NiirrtyException::GetCodeName()` runs into the
     * deprecated constant `E_STRICT` and throws (findings see the journal).
     *
     * @since v0.1.0
     */
    public function testGetErrorMessageCodeZeroNotUsable()
    {

        $ex = new FolderNotFoundException( '/probe/folder', 'ap', 0 );

        $this->assertSame( 0, $ex->getCode() );

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

        $prev = new FolderNotFoundException( '/prev/folder' );
        $ex = new FolderNotFoundException( '/f', 'nested', 256, $prev );

        // Default: separator is a single space:
        $this->assertSame( 'ERROR(256): There was a error with path [/f]! The Folder not exists. nested ERROR(256): There was a error with path [/prev/folder]! The Folder not exists.', $ex->getErrorMessage() );

        // With new line separator:
        $expected = 'ERROR(256): There was a error with path [/f]! The Folder not exists. nested' . "\n" . 'ERROR(256): There was a error with path [/prev/folder]! The Folder not exists.';
        $this->assertSame( $expected, $ex->getErrorMessage( true ) );

        // A non-Niirrty previous exception is included by its message only:
        $ex = new FolderNotFoundException( '/f', null, 256, new \Exception( 'plain previous' ) );
        $this->assertSame( 'ERROR(256): There was a error with path [/f]! The Folder not exists. plain previous', $ex->getErrorMessage() );

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

        $ex = new FolderNotFoundException( '/probe/folder', 'appendix' );

        $str = (string) $ex;

        $this->assertStringContainsString( 'Niirrty\\IO\\FolderNotFoundException', $str );
        $this->assertStringContainsString( 'There was a error with path [/probe/folder]! The Folder not exists. appendix', $str );
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

        $rc = new \ReflectionClass( FolderNotFoundException::class );

        $this->assertSame( 'Niirrty\\IO\\FolderNotFoundException', $rc->getName() );
        $this->assertSame( 'Niirrty\\IO\\IOException', $rc->getParentClass()->getName() );
        $this->assertFalse( $rc->isAbstract() );
        $this->assertSame( [], $rc->getConstants() );
        $this->assertStringEndsWith( '/src/FolderNotFoundException.php', $rc->getFileName() );

        // Findings: in contrast to `Niirrty\IO\FolderAccessException` the class
        // declares NO own method beside the constructor - no factory method and
        // no own accessor like `getAccessType()` exists.
        $names = [];
        foreach( $rc->getMethods() as $rm )
        {
            if( $rm->getDeclaringClass()->getName() !== $rc->getName() )
            {
                continue;
            }
            $names[] = $rm->getName();
        }
        $this->assertSame( ['__construct'], $names );

        $rm = new \ReflectionMethod( FolderNotFoundException::class, '__construct' );
        $ps = $rm->getParameters();

        $this->assertSame( 4, \count( $ps ) );
        $this->assertSame( 'folder', $ps[0]->getName() );
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
        $this->assertStringEndsWith( '/src/FolderNotFoundException.php', $rm->getFileName() );

        // The `$code` parameter defaults to the constant `\E_USER_ERROR`:
        $this->assertSame( 256, $ps[2]->getDefaultValue() );
        $this->assertNull( $ps[1]->getDefaultValue() );
        $this->assertNull( $ps[3]->getDefaultValue() );

        // In contrast to the promoted `$path` of `Niirrty\IO\IOException` the
        // `$folder` parameter is NO promoted property:
        $this->assertFalse( $ps[0]->isPromoted() );

        // The class declares no own property: the `$path` property belongs to
        // the promoted ctor parameter of `Niirrty\IO\IOException`:
        $this->assertSame( [], $rc->getStaticProperties() );
        $props = [];
        $allProps = [];
        foreach( $rc->getProperties() as $rp )
        {
            $allProps[] = $rp->getName();
            if( $rp->getDeclaringClass()->getName() === $rc->getName() )
            {
                $props[] = $rp->getName();
            }
        }
        // Findings: the class itself declares NO own property, the promoted
        // `$path` property belongs to the constructor of `Niirrty\IO\IOException`,
        // the ones behind it are inherited from `Niirrty\NiirrtyException` and
        // the built-in `Exception`:
        $this->assertSame( [], $props );
        $this->assertContains( 'path', $allProps );
        $this->assertContains( '_trace', $allProps );
        $this->assertSame( 'Niirrty\IO\IOException', $rc->getProperty( 'path' )->getDeclaringClass()->getName() );

        // `getPath()` is inherited from `Niirrty\IO\IOException`:
        $pm = new \ReflectionMethod( FolderNotFoundException::class, 'getPath' );
        $this->assertStringEndsWith( '/src/IOException.php', $pm->getFileName() );
        $this->assertTrue( $pm->isFinal() );
        $this->assertSame( 0, $pm->getNumberOfParameters() );

        // `getMessage()` is NOT overridden by any Niirrty class:
        $mm = new \ReflectionMethod( FolderNotFoundException::class, 'getMessage' );
        $this->assertSame( 'Exception', $mm->getDeclaringClass()->getName() );

        // `getErrorMessage()` comes from `Niirrty\NiirrtyException`:
        $gm = new \ReflectionMethod( FolderNotFoundException::class, 'getErrorMessage' );
        $this->assertSame( 'Niirrty\NiirrtyException', $gm->getDeclaringClass()->getName() );
        $this->assertSame( 1, $gm->getNumberOfParameters() );
        $this->assertSame( 0, $gm->getNumberOfRequiredParameters() );

    }

    #endregion


}

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


use Niirrty\IO\Path;
use Niirrty\NiirrtyException;
use Niirrty\PhpException;
use PHPUnit\Framework\TestCase;


/**
 * Test case for the class `Niirrty\IO\Path`.
 *
 * The source in `src/` is NOT changed, the tests assert the CURRENT behaviour
 * (see the "Findings" section of `journal/08-path.md`). Most important: the
 * class does only use the ordinary dot notation syntax, no leading backslash
 * namespace part, so on PHP 8.5 the FIRST class contact throws a
 * `Niirrty\PhpException` (the deprecated, implicitly nullable parameter
 * `$next2` of `Combine()` results in a notice, what Niirrty.Core turns into an
 * exception). Any later class contact uses the class as usual.
 *
 * @since v0.1.0
 */
class PathTest extends TestCase
{


    #region // = = =   T E S T   M E T H O D S  = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Test the FIRST use of the class definition: `Combine()` declares its
     * optional `$next2` parameter as `string $next2 = null`, what is
     * implicitly nullable and therefore deprecated on PHP 8.5. Niirrty.Core's
     * global error handler turns that notice into a thrown
     * `Niirrty\PhpException` - just like `Niirrty\IO\FileAccessException`.
     *
     * That notice is thrown only once per process, so this test must stay the
     * FIRST test method, all following tests use the class as usual.
     *
     * @since v0.1.0
     */
    public function testFirstUseOfClassDefinition()
    {

        $this->expectException( PhpException::class );
        $this->expectExceptionMessage( 'Implicitly marking parameter $next2 as nullable is deprecated' );

        Path::Normalize( '/probe/first' );

    }

    /**
     * Test the class definition by reflection: the class exists, is a plain,
     * final, not abstract class without parent class, interface or trait and
     * it has exactly the 8 methods and the single class-level property.
     *
     * FINDINGS: `isFinal()` is false because the class is declared without the
     * `final` keyword, despite it can not be extended (private ctor).
     *
     * @since v0.1.0
     */
    public function testClassDefinitionFacts()
    {

        $rc = new \ReflectionClass( Path::class );

        $this->assertSame( 'Niirrty\\IO\\Path', $rc->getName() );
        $this->assertSame( 'Path', $rc->getShortName() );
        $this->assertRegExp( '%src/Path.php%', $rc->getFileName() );
        $this->assertFalse( $rc->isInterface() );
        $this->assertFalse( $rc->isAbstract() );
        $this->assertFalse( $rc->isInstantiable() );
        $this->assertFalse( $rc->isFinal() );
        $this->assertEmpty( $rc->getTraitNames() );
        $this->assertEmpty( $rc->getInterfaceNames() );
        $this->assertSame( [], $rc->getConstants() );
        $this->assertCount( 8, $rc->getMethods() );
        $this->assertCount( 1, $rc->getProperties() );
        $this->assertTrue( \class_exists( 'Niirrty\IO\Path' ) );

    }

    /**
     * Test the private constructor: the class can not be instantiated, on PHP
     * 8.5 that is a plain `\Error`, not a `Niirrty\PhpException`.
     *
     * @since v0.1.0
     */
    public function testConstructorNotUsable()
    {

        $this->expectException( \Error::class );
        $this->expectExceptionMessage( 'Call to private Niirrty\IO\Path::__construct()' );

        new Path();

    }

    /**
     * Test the class-level (static) property `NoFolderSeparator`: it is
     * declared without any default value, so it is uninitialized as long as
     * the class definition has not been executed the first time.
     *
     * FINDINGS: the class definition is executed a.t.m. the FIRST use of the
     * class and because of the implicit-nullable deprecation this execution is
     * interrupted with an exception, so the property initializer is NOT
     * executed at that first use.
     *
     * @since v0.1.0
     */
    public function testStaticPropertyUninitialized()
    {

        $this->expectException( \Error::class );
        $this->expectExceptionMessage( 'Typed static property Niirrty\IO\Path::$NoFolderSeparator must not be accessed before initialization' );

        $unused = Path::$NoFolderSeparator;

    }


    /**
     * Test the class-level (static) property `NoFolderSeparator` after the
     * class definition has been executed: on Linux (`DIRECTORY_SEPARATOR` is
     * `'/'`) the property holds the foreign separator char, a single backslash.
     *
     * @since v0.1.0
     */
    public function testStaticPropertyNoFolderSeparatorValue()
    {

        // Any successful method call initializes the class-level property.
        Path::Normalize( '/probe/normalize' );

        $this->assertSame( '\\', Path::$NoFolderSeparator );
        $this->assertNotSame( '/', Path::$NoFolderSeparator );
        $this->assertSame( 1, \strlen( Path::$NoFolderSeparator ) );
        $this->assertTrue( '\\' === Path::$NoFolderSeparator );
        $this->assertNotEmpty( Path::$NoFolderSeparator );

    }

    /**
     * Test the class-level property by reflection: it is a public, typed,
     * class-level property without default value and it is NO class constant.
     *
     * @since v0.1.0
     */
    public function testStaticPropertyReflectionFacts()
    {

        $rc = new \ReflectionClass( Path::class );

        $this->assertTrue( $rc->hasProperty( 'NoFolderSeparator' ) );
        $this->assertFalse( $rc->hasConstant( 'NoFolderSeparator' ) );
        $this->assertEmpty( $rc->getConstants() );

        $props = $rc->getProperties();
        $this->assertCount( 1, $props );
        foreach( $props as $prop )
        {
            $this->assertSame( 'NoFolderSeparator', $prop->getName() );
            $this->assertTrue( $prop->isStatic() );
            $this->assertTrue( $prop->isPublic() );
            $this->assertFalse( $prop->isPrivate() );
            $this->assertFalse( $prop->isProtected() );
            $this->assertTrue( $prop->hasType() );
            $this->assertSame( 'string', $prop->getType()->__toString() );
            $this->assertFalse( $prop->hasDefaultValue() );
            $this->assertFalse( $prop->isVirtual() );
            $this->assertFalse( $prop->isDynamic() );
        }

    }

    /**
     * Test the class-level property with the class constant syntax:
     * `Path::NoFolderSeparator` is NOT a class constant, so it is a plain
     * `\Error` ("Undefined constant").
     *
     * @since v0.1.0
     */
    public function testStaticPropertyIsNoClassConstant()
    {

        $this->assertFalse( \defined( '\Niirrty\IO\Path::NoFolderSeparator' ) );

        $this->expectException( \Error::class );
        $this->expectExceptionMessage( 'Undefined constant Niirrty\\IO\\Path::NoFolderSeparator' );

        $unused = Path::NoFolderSeparator;

    }

    /**
     * Test `Combine()` with 2 parameters: the base path and the next part are
     * joined with the unix separator char `Path::$NoFolderSeparator`.
     *
     * @since v0.1.0
     */
    public function testCombineTwoParts()
    {

        $this->assertSame( '/etc/passwd', Path::Combine( '/etc', 'passwd' ) );
        $this->assertSame( 'a/b', Path::Combine( 'a', 'b' ) );
        $this->assertSame( 'C:/Temp', Path::Combine( 'C:', 'Temp' ) );

        // Leading and trailing separator chars are removed: no separator char
        // is doubled and an absolute next part is treated like a relative one.
        $this->assertSame( '/a/b', Path::Combine( '/a', '/b' ) );
        $this->assertSame( '/a/b', Path::Combine( '/a/', 'b' ) );
        // FINDINGS: only the part borders are trimmed so a trailing
        // separator char of the last part is kept:
        $this->assertSame( '/a/b/', Path::Combine( '/a/', '/b/' ) );
        $this->assertSame( 'a/b', Path::Combine( 'a/', '/b' ) );

        // The foreign separator char is removed at the part borders.
        $this->assertSame( 'a/b', Path::Combine( 'a' . '\\', '\\' . 'b' ) );

        // An empty base path or an empty next part result in the separator
        // char alone or the path without any change.
        $this->assertSame( '/', Path::Combine( '', '' ) );
        $this->assertSame( '/', Path::Combine( '\\', '\\' ) );
        $this->assertSame( 'a/', Path::Combine( 'a', '' ) );
        $this->assertSame( '/a', Path::Combine( '', 'a' ) );

        // A `0` part is kept because it is a non-empty string.
        $this->assertSame( 'a/0', Path::Combine( 'a', '0' ) );

    }

    /**
     * Test `Combine()` with the optional 3rd parameter and with more path
     * parts: the result equals the 2-parameter form joined again.
     *
     * @since v0.1.0
     */
    public function testCombineMoreParts()
    {

        $this->assertSame( 'a/b/c', Path::Combine( 'a', 'b', 'c' ) );
        $this->assertSame( '/a/b/c', Path::Combine( '/a', '/b/', '/c' ) );
        $this->assertSame( 'C:/Temp/sub', Path::Combine( 'C:', '/Temp/', '/sub' ) );
        // FINDINGS: more parameters than defined in the method signature are
        // silently ignored:
        $this->assertSame( 'C:/Temp/sub', Path::Combine( 'C:', 'Temp', 'sub', 'dir' ) );

        // FINDINGS: an empty 3rd parameter is ignored, the result is the same
        // like without that parameter - and the same like `CombineMany()`.
        $this->assertSame( Path::Combine( 'a', 'b' ), Path::Combine( 'a', 'b', '' ) );
        $this->assertSame( 'a/b', Path::Combine( 'a', 'b', '' ) );
        // FINDINGS: different to `CombineMany()` a `0` 3rd parameter is lost.
        $this->assertSame( 'a/b', Path::Combine( 'a', 'b', '0' ) );
        $this->assertSame( 'a//c', Path::Combine( 'a', '', 'c', '' ) );
        // FINDINGS: an empty 3rd part is kept as empty part.

    }

    /**
     * Test `Combine()` with empty parts and whitespaces.
     *
     * FINDINGS: the parameters are trimmed with the chars `'\r\n\t /\'` only,
     * so leading and trailing whitespaces are NOT removed.
     *
     * @since v0.1.0
     */
    public function testCombineEmptyPartsAndSpaces()
    {

        $this->assertSame( '  /a/sub  ', Path::Combine( '  /a/  ', '  sub  ' ) );
        $this->assertSame( '  /a/sub/x  ', Path::Combine( '  /a/  ', '  sub  ', '  x  ' ) );
        $this->assertNotSame( '  /a/  /  sub  ', Path::Combine( '  /a/  ', '  sub  ' ) );

    }

    /**
     * Test `Combine()` with not-string arguments: because the unit test file
     * and the class definition are both hard typed (`declare( strict_types=1`)
     * a `\TypeError` is thrown.
     *
     * @since v0.1.0
     */
    public function testCombineNotStringArgsTypeError()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #1 ($basePath) must be of type string, int given' );

        $unused = Path::Combine( 4711, 'x' );

    }

    public function testCombineNotStringArg2TypeError()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #2 ($next1) must be of type string, int given' );

        $unused = Path::Combine( 'a', 4711 );

    }

    public function testCombineNotStringArg3TypeError()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #3 ($next2) must be of type ?string, int given' );

        $unused = Path::Combine( 'a', 'b', 4711 );

    }

    /**
     * Test `Combine()` with `null` as 3rd part: the parameter signature is
     * `string $next2 = null` so that `null` is accepted and the 2-parameter
     * form is returned.
     *
     * @since v0.1.0
     */
    public function testCombineNullNext2()
    {

        $this->assertSame( 'a/b', Path::Combine( 'a', 'b', null ) );

    }

    /**
     * Test `Combine()` with too less parameters: the method signature requires
     * at least 2 parameters so a `\ArgumentCountError` is thrown.
     *
     * @since v0.1.0
     */
    public function testCombineTooLessArgs()
    {

        $this->expectException( \ArgumentCountError::class );
        $this->expectExceptionMessage( 'Too few arguments to function Niirrty\IO\Path::Combine()' );

        $unused = Path::Combine( 'a' );

    }

    /**
     * Test `CombineMany()`: unlike `Combine()` this method accepts a variable
     * number of path parts and joins all of them with the unix separator char.
     *
     * @since v0.1.0
     */
    public function testCombineManyParts()
    {

        $this->assertSame( 'a', Path::CombineMany( 'a' ) );
        $this->assertSame( '', Path::CombineMany( '' ) );
        $this->assertSame( 'a/b', Path::CombineMany( 'a', 'b' ) );
        $this->assertSame( 'a/b/c', Path::CombineMany( 'a', 'b', 'c' ) );
        $this->assertSame( '/a/b/c', Path::CombineMany( '/a', '/b', '/c' ) );
        $this->assertSame( 'C:/Temp/sub/dir/', Path::CombineMany( 'C:', '/Temp/', '/sub', 'dir/' ) );
        $this->assertSame( '/srv/assets/img/', Path::CombineMany( '/srv', 'assets', '/img/' ) );

        // Unlike `Combine()` a `0` part is kept because only empty strings
        // are recognized as empty parts.
        $this->assertSame( 'a/b/0', Path::CombineMany( 'a', 'b', '0' ) );
        // FINDINGS: empty parts are NOT skipped, so they result in doubled
        // separator chars and a trailing separator char.
        $this->assertSame( 'a//c/', Path::CombineMany( 'a', '', 'c', '' ) );

    }

    /**
     * Test `CombineMany()` with whitespaces: like `Combine()` the parts are
     * trimmed with the chars `'\r\n\t /\'` only.
     *
     * @since v0.1.0
     */
    public function testCombineManySpaces()
    {

        $this->assertSame( '  /a/sub  ', Path::CombineMany( '  /a/  ', '  sub  ' ) );
        $this->assertSame( '/a/b/c', Path::CombineMany( "/a/\r\n\t", "/b/", "/c" ) );

    }

    /**
     * Test `CombineMany()` without any argument: at least the base path is
     * required so an `\ArgumentCountError` is thrown.
     *
     * @since v0.1.0
     */
    public function testCombineManyTooLessArgs()
    {

        $this->expectException( \ArgumentCountError::class );
        $this->expectExceptionMessage( 'Too few arguments to function Niirrty\IO\Path::CombineMany(), 0 passed' );

        $unused = Path::CombineMany();

    }

    /**
     * Test `Normalize()` on a Linux system: the unix directory separator char
     * `'/'` is used, foreign separator chars (backslash) are replaced with it.
     *
     * @since v0.1.0
     */
    public function testNormalizeUnix()
    {

        $this->assertSame( '/a/b/c', Path::Normalize( '/a/b/c' ) );
        $this->assertSame( '/a/b', Path::Normalize( '/a/b/' ) );
        $this->assertSame( '/a/b', Path::Normalize( '/a/b//' ) );
        $this->assertSame( '', Path::Normalize( '' ) );
        $this->assertSame( '', Path::Normalize( '\\\\' ) );
        $this->assertSame( '', Path::Normalize( '//' ) );
        $this->assertSame( '//a', Path::Normalize( "\\\\a\\" ) );
        $this->assertSame( '  /a/b  ', Path::Normalize( '  /a/b  ' ) );

    }

    /**
     * Test `Normalize()` with Windows paths on a Linux system.
     *
     * @since v0.1.0
     */
    public function testNormalizeWindows()
    {

        $this->assertSame( 'C:/Temp/sub', Path::Normalize( "C:\\Temp\\sub" ) );
        $this->assertSame( 'C:/Temp', Path::Normalize( "C:\\Temp\\" ) );
        $this->assertSame( 'C:/Temp', Path::Normalize( 'C:/Temp/' ) );
        $this->assertSame( 'C:/Temp//sub', Path::Normalize( "C:\\Temp\\/sub" ) );

    }

    /**
     * Test `Normalize()` with `'.'` parts: only the pattern `'/./'` is
     * replaced with a single separator char.
     *
     * FINDINGS: a trailing `'.'` part is NOT removed; the pattern `'./'` at
     * the path end is not replaced.
     * FINDINGS: the pattern `'/../'` is NOT replaced so a normalize() call
     * doesn't change the path length in that case.
     *
     * @since v0.1.0
     */
    public function testNormalizeDotParts()
    {

        $this->assertSame( '/a/b', Path::Normalize( '/a/./b' ) );
        $this->assertSame( '/a/./b', Path::Normalize( '/a/././b' ) );
        $this->assertSame( 'a/.', Path::Normalize( 'a/.' ) );
        $this->assertSame( '/a/./b', Path::Normalize( '/a\\.\\.\\b' ) );
        // FINDINGS: parent dir parts are NOT resolved:
        $this->assertStringContainsString( '..', Path::Normalize( '/a/../b' ) );
        $this->assertSame( '/a/../b', Path::Normalize( '/a/../b' ) );
        $this->assertNotSame( '/b', Path::Normalize( '/a/../b' ) );

    }

    /**
     * Test `Unixize()`: every backslash is switched to a unix separator char,
     * independent from its position in the path definition.
     *
     * FINDINGS: unlike `Normalize()` the method does NOT remove a trailing
     * separator char, so a Windows path with a trailing backslash results in a
     * path with a trailing slash.
     *
     * @since v0.1.0
     */
    public function testUnixizePaths()
    {

        $this->assertSame( 'a/b', Path::Unixize( 'a\b' ) );
        $this->assertSame( 'C:/Temp/sub', Path::Unixize( 'C:\Temp\sub' ) );
        $this->assertSame( 'C:/Temp/', Path::Unixize( "C:\\Temp\\" ) );
        $this->assertSame( '/srv/share', Path::Unixize( '\\srv\share' ) );
        $this->assertSame( '//srv//share', Path::Unixize( "\\\\srv\\\\share" ) );
        $this->assertSame( '/a/b', Path::Unixize( '/a/b' ) );
        $this->assertSame( 'a///b', Path::Unixize( 'a/\/b' ) );
        $this->assertSame( '', Path::Unixize( '' ) );
        $this->assertSame( '/', Path::Unixize( "\\" ) );
        $this->assertSame( 'C:/Temp/sub', Path::Normalize( 'C:\Temp\sub' ) );
        $this->assertSame( Path::Normalize( 'C:\Temp\sub' ), Path::Unixize( 'C:\Temp\sub' ) );

    }

    /**
     * Test `IsAbsolute()` on a Linux system with the default parameter
     * `dependToOS = true`: only a leading unix separator char is recognized.
     *
     * FINDINGS: because the method is NOT really system independent, a Windows
     * path definition (`C:\...`) is NOT absolute on a Linux system.
     *
     * @since v0.1.0
     */
    public function testIsAbsoluteLinux()
    {

        $this->assertTrue( Path::IsAbsolute( '/abs/path' ) );
        $this->assertTrue( Path::IsAbsolute( '/' ) );
        $this->assertTrue( Path::IsAbsolute( '//srv/share' ) );
        $this->assertFalse( Path::IsAbsolute( 'rel/path' ) );
        $this->assertFalse( Path::IsAbsolute( '' ) );
        $this->assertFalse( Path::IsAbsolute( 'C:\Temp' ) );
        $this->assertFalse( Path::IsAbsolute( '\srv\share' ) );

    }

    /**
     * Test `IsAbsolute()` with the 2nd parameter `dependToOS = false`: in this
     * case Windows AND Unix path definitions are recognized as absolute.
     *
     * FINDINGS: with `dependToOS = false` an empty path definition is NOT
     * absolute but a Windows path definition is absolute on a Linux system.
     *
     * @since v0.1.0
     */
    public function testIsAbsoluteNotOsDependent()
    {

        $this->assertTrue( Path::IsAbsolute( '/abs/path', false ) );
        $this->assertTrue( Path::IsAbsolute( 'C:\Temp', false ) );
        $this->assertTrue( Path::IsAbsolute( "\\\\srv\\\\share", false ) );
        $this->assertFalse( Path::IsAbsolute( "\\\\srv\\\\share" ) );
        $this->assertTrue( Path::IsAbsolute( 'C:/Temp', false ) );
        $this->assertFalse( Path::IsAbsolute( '', false ) );
        $this->assertFalse( Path::IsAbsolute( 'rel/path', false ) );

        // FINDINGS: with dependToOS = false the 2nd char of the path is
        // checked so a 1-char path definition throws an "Uninitialized string
        // offset" - what is converted to a Niirrty PHP-Exception.
        $this->expectException( PhpException::class );
        $this->expectExceptionMessage( 'Uninitialized string offset 1' );

        $unused = Path::IsAbsolute( 'C', false );

    }

    /**
     * Test `RemoveWorkingDir()`: if the path definition starts with the
     * current working directory then this prefix is removed.
     *
     * FINDINGS: the method is Unix-only: because the working directory and the
     * path definition are Unixized before compared, a Windows path definition
     * is never equal to a Unix working directory.
     * FINDINGS: the working directory prefix must be followed by a separator
     * char so the working directory itself is returned unchanged.
     *
     * @since v0.1.0
     */
    public function testRemoveWorkingDir()
    {

        $cwd = getcwd();

        $this->assertSame( 'sub/x', Path::RemoveWorkingDir( $cwd . '/sub/x' ) );
        $this->assertSame( 'src/Path.php', Path::RemoveWorkingDir( $cwd . '/src/Path.php' ) );
        $this->assertSame( 'src', Path::RemoveWorkingDir( $cwd . '/src' ) );
        // FINDINGS: the working directory must be followed by a separator char
        // so the working directory itself is returned unchanged:
        $this->assertSame( $cwd, Path::RemoveWorkingDir( $cwd ) );
        $this->assertSame( '/tmp/xyz', Path::RemoveWorkingDir( '/tmp/xyz' ) );
        $this->assertSame( 'rel/sub', Path::RemoveWorkingDir( 'rel/sub' ) );
        $this->assertSame( '', Path::RemoveWorkingDir( '' ) );
        $this->assertSame( 'C:/Temp', Path::RemoveWorkingDir( 'C:\Temp' ) );

    }

    /**
     * Test `GetPathinfo()`: the method is a multibyte-safe `pathinfo()`
     * replacement which returns an array with the keys `dirname`, `basename`,
     * `extension`, `filename` if no (or an unknown) info type was defined.
     *
     * @since v0.1.0
     */
    public function testGetPathinfoArray()
    {

        $this->assertSame(
            [
                'dirname'    => '/etc/tmp',
                'basename'   => 'probe.txt',
                'extension'  => 'txt',
                'filename'   => 'probe',
            ],
            Path::GetPathinfo( '/etc/tmp/probe.txt' )
        );
        $this->assertSame(
            [
                'dirname'    => '',
                'basename'   => '',
                'extension'  => '',
                'filename'   => '',
            ],
            Path::GetPathinfo( '' )
        );
        $this->assertSame(
            [
                'dirname'    => '/etc/tmp',
                'basename'   => 'probe',
                'extension'  => '',
                'filename'   => 'probe',
            ],
            Path::GetPathinfo( '/etc/tmp/probe' )
        );
        $this->assertSame(
            [
                'dirname'    => '/a/b',
                'basename'   => 'c.tar.gz',
                'extension'  => 'gz',
                'filename'   => 'c.tar',
            ],
            Path::GetPathinfo( '/a/b/c.tar.gz' )
        );
        // FINDINGS: a filename which begins with a dot is recognized as
        // extension without filename part:
        $this->assertSame(
            [
                'dirname'    => '/a',
                'basename'   => '.phpunit',
                'extension'  => 'phpunit',
                'filename'   => '',
            ],
            Path::GetPathinfo( '/a/.phpunit' )
        );
        // FINDINGS: unlike `pathinfo()` the result is multibyte safe:
        $this->assertSame(
            [
                'dirname'    => '/srv/üñïc',
                'basename'   => 'äöü.txt',
                'extension'  => 'txt',
                'filename'   => 'äöü',
            ],
            Path::GetPathinfo( '/srv/üñïc/äöü.txt' )
        );
        // FINDINGS: on a Unix system Windows paths are not split:
        $this->assertSame(
            [
                'dirname'    => 'C:\Temp\sub',
                'basename'   => 'probe.txt',
                'extension'  => 'txt',
                'filename'   => 'probe',
            ],
            Path::GetPathinfo( 'C:\Temp\sub\probe.txt' )
        );

    }

    /**
     * Test `GetPathinfo()` with the info type as string name.
     *
     * @since v0.1.0
     */
    public function testGetPathinfoTypeAsString()
    {

        $this->assertSame( '/etc/tmp', Path::GetPathinfo( '/etc/tmp/probe.txt', 'dirname' ) );
        $this->assertSame( 'probe.txt', Path::GetPathinfo( '/etc/tmp/probe.txt', 'basename' ) );
        $this->assertSame( 'txt', Path::GetPathinfo( '/etc/tmp/probe.txt', 'extension' ) );
        $this->assertSame( 'probe', Path::GetPathinfo( '/etc/tmp/probe.txt', 'filename' ) );

    }

    /**
     * Test `GetPathinfo()` with the info type as PATHINFO_* constant.
     *
     * @since v0.1.0
     */
    public function testGetPathinfoTypeInfoAsConst()
    {

        $this->assertSame( '/etc/tmp', Path::GetPathinfo( '/etc/tmp/probe.txt', PATHINFO_DIRNAME ) );
        $this->assertSame( 'probe.txt', Path::GetPathinfo( '/etc/tmp/probe.txt', PATHINFO_BASENAME ) );
        $this->assertSame( 'txt', Path::GetPathinfo( '/etc/tmp/probe.txt', PATHINFO_EXTENSION ) );
        $this->assertSame( 'probe', Path::GetPathinfo( '/etc/tmp/probe.txt', PATHINFO_FILENAME ) );

    }

    /**
     * Test `GetPathinfo()` with an unknown info type: unlike `pathinfo()` the
     * complete info array is returned.
     *
     * @since v0.1.0
     */
    public function testGetPathinfoUnknownType()
    {

        $rv = Path::GetPathinfo( '/etc/tmp/probe.txt', 'unknown' );
        $this->assertIsArray( $rv );
        $this->assertCount( 4, $rv );
        $this->assertSame( '/etc/tmp', $rv[ 'dirname' ] );

        $rv = Path::GetPathinfo( '/etc/tmp/probe.txt', 4711 );
        $this->assertIsArray( $rv );
        $this->assertCount( 4, $rv );
        $this->assertSame( 'probe.txt', $rv[ 'basename' ] );

    }

    /**
     * Test the class definition by reflection: the class name, its methods and
     * their signatures.
     *
     * @since v0.1.0
     */
    public function testClassMethodsByReflection()
    {

        $oi = new \ReflectionClass( Path::class );

        $this->assertSame( 'Niirrty\IO\Path', $oi->getName() );
        $this->assertSame( 'Path', $oi->getShortName() );
        $this->assertSame( 'Niirrty\\IO', $oi->getNamespaceName() );
        $this->assertCount( 8, $oi->getMethods() );

        $found = [];
        foreach( $oi->getMethods() as $om )
        {
            if ( ! $om->isPublic() )
            {
                // FINDINGS: the private constructor is visible by reflection!
                $this->assertSame( '__construct', $om->getName() );
                $this->assertTrue( $om->isPrivate() );
                $this->assertFalse( $om->isStatic() );
                $this->assertFalse( $om->hasReturnType() );
                continue;
            }
            $this->assertTrue( $om->isStatic() );
            $this->assertFalse( $om->isAbstract() );
            $this->assertTrue( $om->hasReturnType() );
            $found[] = $om->getName();
            switch( $om->getName() )
            {
                case 'Combine':
                case 'CombineMany':
                case 'Normalize':
                case 'Unixize':
                case 'RemoveWorkingDir':
                    $this->assertSame( 'string', $om->getReturnType()->__toString() );
                    break;
                case 'IsAbsolute':
                    $this->assertSame( 'bool', $om->getReturnType()->__toString() );
                    break;
                case 'GetPathinfo':
                    $this->assertSame( 'array|string', $om->getReturnType()->__toString() );
                    break;
            }
        }

        $this->assertContains( 'Combine', $found );
        $this->assertContains( 'CombineMany', $found );
        $this->assertContains( 'Normalize', $found );
        $this->assertContains( 'IsAbsolute', $found );
        $this->assertContains( 'Unixize', $found );
        $this->assertContains( 'RemoveWorkingDir', $found );
        $this->assertContains( 'GetPathinfo', $found );

        $this->assertTrue( $oi->hasMethod( 'Combine' ) );
        $this->assertTrue( $oi->hasMethod( 'GetPathinfo' ) );
        $this->assertFalse( $oi->hasMethod( 'Pathinfo' ) );

    }

    #endregion


}

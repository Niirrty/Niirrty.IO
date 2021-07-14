<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2021, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\IO;


/**
 * A static path helper class
 *
 * @since      v0.1
 */
class Path
{


    #region // - - -   P U B L I C   S T A T I C   F I E L D S   - - - - - - - - - - - - - - - - - - -

    /**
     * This directory separator is not used by current system.
     *
     * @var string
     */
    public static string $NoFolderSeparator;

    #endregion


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Inits a new instance.
     */
    private function __construct() { }

    #endregion


    #region // - - -   P U B L I C   S T A T I C   M E T H O D S   - - - - - - - - - - - - - - - - - -

    /**
     * Combine 2 or 3 path elements to a single path and returns it.
     *
     * @param string      $basePath The base path.
     * @param string      $next1    The 2nd path part.
     * @param string|null $next2    Optional 3rd path part
     *
     * @return string
     */
    public static function Combine( string $basePath, string $next1, string $next2 = null ): string
    {

        if ( empty( $next2 ) )
        {
            return (
                \rtrim( $basePath, "\r\n\t /\\" )
                . \DIRECTORY_SEPARATOR
                . \ltrim( $next1, "\r\n\t /\\" )
            );
        }

        return (
            \rtrim( $basePath, "\r\n\t /\\" )
            . \DIRECTORY_SEPARATOR
            . \trim( $next1, "\r\n\t /\\" )
            . \DIRECTORY_SEPARATOR
            . \ltrim( $next2, "\r\n\t /\\" )
        );
    }

    /**
     * Normalizes a path to directory separators, used by current system.
     *
     * @param string $path
     *
     * @return string
     */
    public static function Normalize( string $path ): string
    {

        $isWin = '\\' === DIRECTORY_SEPARATOR;
        if ( empty( static::$NoFolderSeparator ) )
        {
            static::$NoFolderSeparator = $isWin ? '/' : '\\';
        }
        $tmpPath = ( $isWin
            ? \trim(
                \str_replace(
                    static::$NoFolderSeparator,
                    \DIRECTORY_SEPARATOR,
                    $path
                ),
                \DIRECTORY_SEPARATOR
            )
            : \rtrim(
                \str_replace(
                    static::$NoFolderSeparator,
                    \DIRECTORY_SEPARATOR,
                    $path
                ),
                \DIRECTORY_SEPARATOR
            )
        );

        // return the resulting path and replace /./ or \.\ with a single directory separator
        return str_replace(
            \DIRECTORY_SEPARATOR . '.' . \DIRECTORY_SEPARATOR,
            \DIRECTORY_SEPARATOR,
            $tmpPath
        );
    }

    /**
     * Returns if the defined path is a absolute path definition.
     *
     * @param string  $path
     * @param boolean $dependToOS
     *
     * @return boolean
     */
    public static function IsAbsolute( string $path, bool $dependToOS = true ): bool
    {

        if ( \is_null( $path ) || \strlen( $path ) < 1 )
        {
            return false;
        }
        if ( $dependToOS )
        {
            if ( !\IS_WIN )
            {
                return $path[ 0 ] == '/';
            }
            if ( \strlen( $path ) < 2 )
            {
                return false;
            }

            return $path[ 1 ] == ':' || ( $path[ 0 ] == '\\' && $path[ 1 ] == '\\' );
        }

        return $path[ 0 ] == '/' || $path[ 1 ] == ':' || ( $path[ 0 ] == '\\' && $path[ 1 ] == '\\' );
    }

    /**
     * Switches als windows directory separator (backslashes) to unix like (slashes)
     *
     * @param string $path
     *
     * @return string
     */
    public static function Unixize( string $path ): string
    {

        if ( \is_null( $path ) )
        {
            return '';
        }

        return \str_replace( '\\', '/', $path );
    }

    /**
     * Removes the current working directory from defined path, if it starts with it.
     *
     * @param string $path
     *
     * @return string
     */
    public static function RemoveWorkingDir( string $path ): string
    {

        if ( \is_null( $path ) )
        {
            return '';
        }

        $wd = '~^' . \preg_quote( static::Unixize( \getcwd() ) . '/', '~' ) . '~';

        return \preg_replace( $wd, '', static::Unixize( $path ) );

    }

    /**
     * This is a multibyte safe pathinfo() replacement.
     *
     * Drop-in replacement for pathinfo(), but multibyte-safe, cross-platform-safe, old-version-safe.
     * Works similarly to the one in PHP >= 5.2.0
     *
     * @link http://www.php.net/manual/en/function.pathinfo.php#107461
     *
     * @param string              $path     A filename or path, does not need to exist as a file
     * @param integer|string|null $infoType Either a PATHINFO_* constant, or a string name to return only the specified
     *                                  piece, allows 'filename' to work on PHP < 5.2
     *
     * @return string|array
     */
    public static function GetPathinfo( string $path, int|string $infoType = null ): array|string
    {

        $infos = [ 'dirname' => '', 'basename' => '', 'extension' => '', 'filename' => '' ];
        $pathinfo = [];
        if ( preg_match( '%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^.\\\\/]+?)|))[\\\\/.]*$%im', $path, $pathinfo ) )
        {
            if ( array_key_exists( 1, $pathinfo ) )
            {
                $infos[ 'dirname' ] = $pathinfo[ 1 ];
            }
            if ( array_key_exists( 2, $pathinfo ) )
            {
                $infos[ 'basename' ] = $pathinfo[ 2 ];
            }
            if ( array_key_exists( 5, $pathinfo ) )
            {
                $infos[ 'extension' ] = $pathinfo[ 5 ];
            }
            if ( array_key_exists( 3, $pathinfo ) )
            {
                $infos[ 'filename' ] = $pathinfo[ 3 ];
            }
        }

        return match ( $infoType )
        {
            PATHINFO_DIRNAME,   'dirname'   => $infos[ 'dirname' ],
            PATHINFO_BASENAME,  'basename'  => $infos[ 'basename' ],
            PATHINFO_EXTENSION, 'extension' => $infos[ 'extension' ],
            PATHINFO_FILENAME,  'filename'  => $infos[ 'filename' ],
            default                         => $infos,
        };

    }

    #endregion


}


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
 * This exception should be used if accessing a folder for reading and/or writing fails.
 *
 * The class extends from {@see \Niirrty\IO\IOException}.
 *
 * @since v0.1
 */
class FolderAccessException extends IOException
{


    #region // = = =   C O N S T A N T S   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Reading folder access.
     */
    const ACCESS_READ = 'read';

    /**
     * Writing folder access.
     */
    const ACCESS_WRITE = 'write';

    /**
     * Reading and writing folder access.
     */
    const ACCESS_READWRITE = 'read and write';

    /**
     * Creating folder access.
     */
    const ACCESS_CREATE = 'create';

    /**
     * Deleting folder access.
     */
    const ACCESS_DELETE = 'delete';

    #endregion


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Inits a new instance.
     *
     * @param string          $folder   The folder where accessing fails.
     * @param string          $access   The access type (see \UK\IO\FolderAccessException::ACCESS_* class constants)
     * @param string|null     $message  The optional error message
     * @param integer         $code     A optional error code (Defaults to \E_USER_ERROR)
     * @param \Throwable|null $previous A Optional previous exception.
     */
    public function __construct(
        string $folder, private string $access = self::ACCESS_READ, ?string $message = null,
        int $code = 256, ?\Throwable $previous = null )
    {

        parent::__construct(
            $folder,
            \sprintf( 'Could not %s folder.', $access ) . static::appendMessage( $message ),
            $code,
            $previous
        );

    }

    #endregion


    #region // - - -   P U B L I C   M E T H O D S   - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Returns the error folder access type (see \Niirrty\IO\FolderAccessException::ACCESS_* class constants)
     *
     * @return string
     */
    public final function getAccessType(): string
    {

        return $this->access;
    }

    #endregion


    #region // - - -   P U B L I C   S T A T I C   M E T H O D S   - - - - - - - - - - - - - - - - - -

    /**
     * Inits a new \Niirrty\IO\FolderAccessException for folder read mode.
     *
     * @param string          $folder   The folder where reading fails.
     * @param string|null     $message  The optional error message.
     * @param integer         $code     A optional error code (Defaults to \E_USER_ERROR)
     * @param \Throwable|null $previous A Optional previous exception.
     * @return FolderAccessException
     */
    public static function Read(
        string $folder, ?string $message = null, int $code = \E_USER_ERROR, ?\Throwable $previous = null )
    : FolderAccessException
    {

        return new FolderAccessException (
            $folder,
            FolderAccessException::ACCESS_READ,
            $message,
            $code,
            $previous
        );

    }

    /**
     * Inits a new \Niirrty\IO\FolderAccessException for folder write mode.
     *
     * @param string          $folder   The folder where reading fails.
     * @param string|null     $message  The optional error message.
     * @param integer         $code     A optional error code (Defaults to \E_USER_ERROR)
     * @param \Throwable|null $previous A Optional previous exception.
     * @return FolderAccessException
     */
    public static function Write(
        string $folder, ?string $message = null, int $code = \E_USER_ERROR, ?\Throwable $previous = null )
    : FolderAccessException
    {

        return new FolderAccessException (
            $folder,
            FolderAccessException::ACCESS_WRITE,
            $message,
            $code,
            $previous
        );

    }

    #endregion


}


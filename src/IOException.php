<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2021, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.5.0
 */


declare( strict_types=1 );


namespace Niirrty\IO;


use \Niirrty\NiirrtyException;


/**
 * This class defines an exception, used as base exception to all IO exceptions.
 *
 * It extends from {@see NiirrtyException}.
 *
 * @since v0.1.0
 */
class IOException extends NiirrtyException
{


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Init a new instance.
     *
     * @param string          $path     The path, associated with the error.
     * @param string|null     $message  optional error message.
     * @param string|int      $code     The optional error code (Defaults to \E_USER_ERROR)
     * @param \Throwable|null $previous optional previous exception
     */
    public function __construct(
        protected string $path, ?string $message = null, string|int $code = 256, ?\Throwable $previous = null )
    {

        parent::__construct(
            \sprintf( 'There was a error with path [%s]!', $path ) . static::appendMessage( $message ),
            $code,
            $previous
        );

    }

    #endregion


    #region // - - -   P U B L I C   M E T H O D S   - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Returns the path that was error depending.
     *
     * @return string
     */
    public final function getPath(): string
    {

        return $this->path;

    }

    #endregion


}


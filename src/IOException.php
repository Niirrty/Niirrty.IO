<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  ©2017, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\IO;


use \Niirrty\NiirrtyException;


/**
 * This class defines a exception, used as base exception of all IO exceptions.
 *
 * It extends from {@see \Niirrty\NiirrtyException}.
 *
 * @since v0.1.0
 */
class IOException extends NiirrtyException
{


   # <editor-fold desc="= = =   P R O T E C T E D   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = =">"

   /**
    * @type string
    */
   protected $_path;

   # </editor-fold>


   # <editor-fold desc="= = =   C O N S T R U C T O R   +   D E S T R U C T O R   = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    *
    * @param string         $path     The path, associated with the error.
    * @param string         $message  A optional error message.
    * @param integer        $code     The optional error code (Defaults to \E_USER_ERROR)
    * @param \Throwable     $previous A optional previous exception
    */
   public function __construct( string $path, string $message = null, int $code = 256, \Throwable $previous = null )
   {

      parent::__construct(
         \sprintf( 'There was a error with path [%s]!', $path ) . static::appendMessage( $message ),
         $code,
         $previous
      );

      $this->_path = $path;

   }

   # </editor-fold>


   # <editor-fold desc="= = =   G E T T E R S   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Returns the path that was error depending.
    *
    * @return string
    */
   public final function getPath() : string
   {

       return $this->_path;

   }

   # </editor-fold>


}


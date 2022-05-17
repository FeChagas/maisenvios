<?php 
  header('Content-Type: application/json;charset=utf-8');
  include '../connection/mysql.php'; 
?>
<?php 

$where = 'WHERE 1 = 1';
if ($_GET && isset($_GET['shop_id']) && !is_null($_GET['shop_id'])) {
  $where .= " AND shopId = {$_GET['shop_id']}";
}
if ($_GET && isset($_GET['name']) && !is_null($_GET['name'])) {
  $where .= " AND name = '{$_GET['name']}'";
}

$retorno = [];
$busca = "SELECT * FROM `shop_meta` {$where}";
$result = $link->query($busca);
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $row['value'] = maybe_unserialize($row['value']);
    array_push($retorno, $row);
  }
}
echo json_encode($retorno);

/**
 * Unserialize data only if it was serialized.
 */
function maybe_unserialize( $data ) {
  if ( is_serialized( $data ) ) { // Don't attempt to unserialize data that wasn't serialized going in.
      return @unserialize( trim( $data ) );
  }
  return $data;
}

/**
 * Check value to find if it was serialized.
 */
function is_serialized( $data, $strict = true ) {
  // If it isn't a string, it isn't serialized.
  if ( ! is_string( $data ) ) {
      return false;
  }
  $data = trim( $data );
  if ( 'N;' === $data ) {
      return true;
  }
  if ( strlen( $data ) < 4 ) {
      return false;
  }
  if ( ':' !== $data[1] ) {
      return false;
  }
  if ( $strict ) {
      $lastc = substr( $data, -1 );
      if ( ';' !== $lastc && '}' !== $lastc ) {
          return false;
      }
  } else {
      $semicolon = strpos( $data, ';' );
      $brace     = strpos( $data, '}' );
      // Either ; or } must exist.
      if ( false === $semicolon && false === $brace ) {
          return false;
      }
      // But neither must be in the first X characters.
      if ( false !== $semicolon && $semicolon < 3 ) {
          return false;
      }
      if ( false !== $brace && $brace < 4 ) {
          return false;
      }
  }
  $token = $data[0];
  switch ( $token ) {
      case 's':
          if ( $strict ) {
              if ( '"' !== substr( $data, -2, 1 ) ) {
                  return false;
              }
          } elseif ( false === strpos( $data, '"' ) ) {
              return false;
          }
          // Or else fall through.
      case 'a':
      case 'O':
          return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
      case 'b':
      case 'i':
      case 'd':
          $end = $strict ? '$' : '';
          return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
  }
  return false;
}
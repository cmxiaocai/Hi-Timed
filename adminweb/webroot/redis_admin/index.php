<?php
$config = array(
    // 'default' => array(
    //    'host'     => '192.168.12.153',
    //    'port'     => 6380,
    //    'database' => 0,
    // ),
    'default' => array(
       'host'     => '172.17.163.80',
       'port'     => 8777,
       'database' => 0,
    ),
);

$script_name =  "index.php";
$server      =  isset( $_GET["server"] )  ? $_GET["server"]   : 'default';
$db          =  isset( $_GET["db"] )      ? $_GET["db"]       : 0;
$action      =  isset( $_GET["action"] )  ? $_GET['action']   : 'list';
$pattern     =  isset( $_GET['pattern'] ) ? $_GET['pattern']  : '';

$Redis = new Redis();
$Redis->connect($config[$server]['host'], $config[$server]['port'], 5);

try {
    $Redis->ping();
} catch( Exception $e ) {
    die("Couldn't connect to server [tcp://{$config[$server]['host']}:{$config[$server]['port']}]. " . $e->getMessage());
}

$Redis->select( $db );
if ( isset ( $_GET['key'] ) ) {
    $Json = new Json($server, $db);
    $html = $Json->renderKey($Redis);
    exit ( json_encode(array('html' => $html) ) );
}
$Html = new Html($server, $db);
$Html->setScriptName($script_name);
$Html->setAction($action);
$Html->setPattern($pattern);
$Html->setServerList($config);

class Json {

    public function __construct($server, $db) {
        $this->server = $server;
        $this->db     = $db;
    }

    public function renderKey($Redis) {
        $key    = base64_decode( $_GET['key'] );
        $ttl    = $Redis->ttl( $key );
        $type   = $Redis->type( $key );
        $retval = false;
        switch( $type ) {
            case 1: {
                $retval = sprintf('<div style="word-break:break-all;word-wrap:break-word;">%s</div>',  $Redis->get( $key ) );
                $type   = 'string';
                $size   = strlen($retval) . " characters";
                break;
            }
            case 2: {
                $set = $Redis->smembers( $key );
                $retval = '<table class="table table-bordered table-condensed table-striped">';
                foreach ($set as $value) {
                    $retval .= sprintf("<tr><td>%s</td></tr>", $value);
                }
                $retval.= '</table>';
                $size   = count($set) . " items";
                $type   = 'set';
                break;
            }
            case 3: {
                $list = $Redis->lrange( $key, 0, -1 );
                $retval = '<table class="table table-bordered table-condensed table-striped"><tr><th width="20%">Index</th><th>Value</th></tr>';
                foreach ($list as $index => $value) {
                    $retval .= sprintf("<tr><td>%d</td><td>%s</td></tr>", $index, $value);
                }
                $retval.= '</table>';
                $size   = count($list) . " items";
                $type   = 'list';
                break;
            }
            case 4: {
                $zset   = $Redis->zrange( $key, 0, -1, "WITHSCORES" );
                $retval = '<table class="table table-bordered table-condensed table-striped"><tr><th width="20%">Score</th><th>Value</th></tr>';
                foreach ($zset as $value => $scroe) {
                    $retval .= sprintf("<tr><td>%d</td><td>%s</td></tr>", $scroe, $value);
                }
                $retval.= '</table>';
                $size   = count($zset) . " items";
                $type   = 'zset';
                break;
            }
            case 5: {
                $hash = $Redis->hgetall( $key );
                $retval = '<table class="table table-bordered table-condensed table-striped"><tr><th width="20%">Key</th><th>Value</th></tr>';
                foreach ($hash as $k => $value) {
                    $retval .= sprintf("<tr><td>%s</td><td>%s</td></tr>", $k, $value);
                }
                $type   = 'hash';
                $size   = count($hash) . " items";
                break;
            }
            default: {
                $retval = "Key not exists.";
                $type   = 'none';
                break;
            }
        }
        // class="dl-horizontal"
        $html = '<style>.pd-key{padding:5px;} 
            #detail .pd-left{width:70px;} 
            #detail .pd-right{margin-left:90px;}
            #detail td,#detail th {font-size:12px;}
            </style>
            <dl class="dl-horizontal">
                <dt class="pd-key pd-left"><code>Key</code></dt>
                <dd class="pd-key pd-right"> ' . $key . ' </dd>
                <dt class="pd-key pd-left"><code>TTL</code></dt>
                <dd class="pd-key pd-right"> ' . ( ($ttl == -1) ? 'does not expire' : $ttl ) . ' </dd>
                <dt class="pd-key pd-left"><code>Type</code></dt>
                <dd class="pd-key pd-right"> ' . $type . ' </dd>
                <dt class="pd-key pd-left"><code>Size</code></dt>
                <dd class="pd-key pd-right"> ' . $size . ' </dd>
                <dt class="pd-key pd-left"><code>Value</code></dt>
                <dd class="pd-key pd-right">' . $retval . '</dd>
            </dl>';
        return $html;
    }
}

class Html {

    public function __construct($server, $db) {
        $this->server = $server;
        $this->db     = $db;
    }

    public function setScriptName($script_name) {
        $this->script_name = $script_name;
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public function setPattern($pattern) {
        $this->pattern = $pattern;
    }

    public function setServerList($list) {
        $this->list = $list;
    }

    public function renderServerForm() {
        $html  = sprintf('<form class="navbar-form" method="get" action="%s">', $this->script_name );
        $html .= '<select name="server" id="server" class="form-control" onchange="this.form.submit();">';
        foreach ($this->list as $key => $config) {
            $html .= sprintf('<option value="%s" %s>%s</option>', $key, ($key == $this->server ? 'selected' : ''), $key);
        }
        $html .= '</select></form>';
        return $html;
    }

    public function renderDatabasesForm() {
        $html = '<ul class="nav navbar-nav">';
        for ($i=0; $i < 16; $i++) {
            if ( $i == $this->db && $this->action != 'info' ) 
                $html .= sprintf('<li class="active"><a href="javascript:;">[#%d]</a></li>', $i);
            else
                $html .= sprintf('<li><a href="%s?server=%s&db=%d">[#%d]</a>', $this->script_name, $this->server, $i, $i);
        }
        if ( $this->action != 'info')
            $html .= sprintf('<li><a href="%s?server=%s&action=info">[INFO]</a></li>', $this->script_name, $this->server);
        else
            $html .= '<li class="active"><a href="javascript:;">[INFO]</a></li>';
        $html .= "</ul>";
        return $html;
    }

    public function renderList($Redis) {
        $count_all_keys_in_db = $Redis->dbsize();
        $matched_keys         = $Redis->keys( $this->pattern );

        $html  = sprintf('<form name="form-list" class="form-inline" method="get" action="%s">', $this->script_name );
        $html .= sprintf('<div class="panel panel-default">');
        $html .= sprintf('<div class="panel-heading">');
        $html .= sprintf('<div class="row">');

        $html .= sprintf('<div class="form-inline col-md-7">');
        $html .= sprintf('<div class="form-group">');
        $html .= sprintf('<label for="pattern">Pattern </label> ');
        $html .= sprintf('<input class="form-control" type="text" name="pattern" id="pattern" placeholder="Key, like:*sesson*" value="%s" /> ', $this->pattern);
        $html .= sprintf('<button type="submit" class="btn btn-default">Submit</button>');
        $html .= sprintf('</div></div>');

        $html .= sprintf('<div class="col-md-5">Showing %d of %d keys. ', count( $matched_keys ), $count_all_keys_in_db);
        $html .= '<select name="action" class="form-control">
            <option value="list">Operator</option>
            <option value="delete">Delete selected</option>
        </select>
        <button class="btn btn-default" type="submit" onClick="return confirmSubmit()">Execute</button>
        </div>';

        $html .= '</div>';
        $html .= '</div></div>';

        $html .= sprintf('<input type="hidden" name="server" value="%s" />', $this->server);
        $html .= sprintf('<input type="hidden" name="db" value="%s" />', $this->db);
        $html .= '
        <div class="row">
        <div id="list" class="col-md-6" style="overflow:auto;";>
            <div class="list-group">
            <a class="list-group-item disabled" href="javascript:;"><input type="checkbox" name="check_all" onClick="selectToggle(\'form-list\')" /> Key </a>
            ';
        foreach( $matched_keys as $i => $key ) {
            $html .= sprintf('<a class="list-group-item" href="javascript:;" data-type="key" data-href="%s?server=%s&db=%d&action=list&pattern=%s&key=%s"><input type="checkbox" name="item[]" value="%s" /> %s </a>', $this->script_name, $this->server, $this->db, $this->pattern, htmlspecialchars( base64_encode( $key ) ), htmlspecialchars( base64_encode( $key ) ), $key);
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="col-md-6"><div class="well" id="detail"><p>Please choose a key from left.</p></div></div></form>';
        return $html;
    }

    public function renderInfo($Redis) {
        $info = $Redis->info();
        $html = sprintf('<div class="alert alert-info">Last save %d seconds ago.</div>', time() - $Redis->lastsave());
        $html.= '<table class="table table-bordered table-condensed">';
        foreach( $info as $key => $value ) {
            $html .= '<tr><td>' . $key . "</td>";
            $html .= '<td>' .  $value . "</td></tr>";
        }
        $html .= "</table>";
        return $html;
    }

    public function renderAction($Redis) {
        if ($this->action == 'info') {
            return $this->renderInfo($Redis);
        } elseif ( $this->action == 'list') {
            return $this->renderList($Redis);
        } elseif ( $this->action == 'delete' ) {
            if ( isset( $_GET['item'] ) && count( $_GET['item'] ) > 0 ) {
                foreach( $_GET['item'] as $key )
                    $Redis->del( base64_decode( $key ) );
            }
            return $this->renderList($Redis);
        }
    }
}
?>
<!DOCTYPE html>
<head>
    <title>Adminer for Redis</title>
</head>
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<body>

<div class="container">
    <nav class="navbar navbar-default navbar-static-top" style="margin-bottom:0">
      <div class="container-fluid">
        <div class="navbar-right">
          <a class="navbar-brand" href="./">Redis Adminer</a>
        </div>
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <a class="navbar-brand" href="./">Server</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse">
            <?php echo $Html->renderServerForm();?>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>


    <nav class="navbar navbar-default navbar-static-top">
      <!-- We use the fluid option here to avoid overriding the fixed width of a normal container within the narrow content columns. -->
      <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand" href="javascript:;">Databases</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse">
          <?php echo $Html->renderDatabasesForm();?>
        </div><!-- /.navbar-collapse -->
      </div>
    </nav>

    <?php echo $Html->renderAction($Redis);?>
</div>
<script type="text/javascript">
function selectToggle(n) {
    var fo = document.forms[n];
    t = fo.elements['check_all'].checked;
    for( var i=0; i < fo.length; i++ )  {
        if( fo.elements[i].name == 'check_all' ) {
            continue;
        }

        fo.elements[i].checked = t ? "checked" : "";
    }
}
function confirmSubmit() {
    if ( document.forms['form-list'].action.selectedIndex == 0 ) {
        return true;
    }
    return confirm("Are you sure you wish to continue?");
}
jQuery("#list").height($(window).height() - $('#list').offset().top - 20);
jQuery('a[data-type=key]').bind('click', function() {
    jQuery('a[data-type=key]').removeClass('active');
    jQuery(this).addClass('active');
    jQuery('#detail').html('<div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 100%"><span class="sr-only">Loading</span></div></div>');
    jQuery.getJSON( jQuery(this).attr('data-href'), function(response) {
        jQuery('#detail').html( response.html );
    })
}); 

</script>
</body>
</html>

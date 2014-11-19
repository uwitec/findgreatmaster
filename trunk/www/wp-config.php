<?php
/**
 * WordPress基础配置文件。
 *
 * 本文件包含以下配置选项：MySQL设置、数据库表名前缀、密钥、
 * WordPress语言设定以及ABSPATH。如需更多信息，请访问
 * {@link http://codex.wordpress.org/zh-cn:%E7%BC%96%E8%BE%91_wp-config.php
 * 编辑wp-config.php}Codex页面。MySQL设置具体信息请咨询您的空间提供商。
 *
 * 这个文件被安装程序用于自动生成wp-config.php配置文件，
 * 您可以手动复制这个文件，并重命名为“wp-config.php”，然后填入相关信息。
 *
 * @package WordPress
 */

// ** MySQL 设置 - 具体信息来自您正在使用的主机 ** //
/** WordPress数据库的名称 */
define('DB_NAME', 'yzx');

/** MySQL数据库用户名 */
define('DB_USER', 'yzxmanager');

/** MySQL数据库密码 */
define('DB_PASSWORD', 'ueg892eczzu22');

/** MySQL主机 */
define('DB_HOST', 'rdsyqeirbiqjqfe.mysql.rds.aliyuncs.com');

/** 创建数据表时默认的文字编码 */
define('DB_CHARSET', 'utf8');

/** 数据库整理类型。如不确定请勿更改 */
define('DB_COLLATE', '');

/**#@+
 * 身份认证密钥与盐。
 *
 * 修改为任意独一无二的字串！
 * 或者直接访问{@link https://api.wordpress.org/secret-key/1.1/salt/
 * WordPress.org密钥生成服务}
 * 任何修改都会导致所有cookies失效，所有用户将必须重新登录。
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'IQ.G$|S+%QuE-3(}kL$u1ST}1Uo$KPE25($ W!`wRzP:x^+:@7qFAP|*+Xx^722*');
define('SECURE_AUTH_KEY',  '`s%a hc-f-o,ML&)-(6-^b)2W^YaSn-6zvA]ktX/)835}.]mT[ uG jgYreHNi~-');
define('LOGGED_IN_KEY',    'z*m:O%3)W 4F.=r?wh:^VC3Z@?WJM`^*gwn9Y@*9&2I5H;DVuI*#H@G%F>$.IAOc');
define('NONCE_KEY',        '|Jv^558K`FzzN:eg7l)02c#c+>6zE#wFcn/-O!pWeFou]zFhj(:H+|D7E`/)[^XW');
define('AUTH_SALT',        '7w+XUG-6uY(B7Zg1b2lqy@3;Q&aMgHq3GpvCZ/fS}Y=8;hU[gs/pSAL?|:UD.P,`');
define('SECURE_AUTH_SALT', 'MWi}^2KnxXm.`!q-{ DHSp;NZK}9GU}|0iwnaV0,,BFr,<>DlM8[TtF7Ph+~2;2K');
define('LOGGED_IN_SALT',   'XRey1D{+Q_=Emyh~* CM)I8;.1mR6#-jNj;3OjQ5|w!#[KY_C`v_gdI-q:-^~n#=');
define('NONCE_SALT',       '1iSh;Fl6yRJ9.2Qu~-n.Em`K|d^]{^;^[TkX@;p9+D3J3~t!+,H`J-JkeHlj-~yS');

/**#@-*/

/**
 * WordPress数据表前缀。
 *
 * 如果您有在同一数据库内安装多个WordPress的需求，请为每个WordPress设置
 * 不同的数据表前缀。前缀名只能为数字、字母加下划线。
 */
$table_prefix  = 'tds_';

/**
 * 开发者专用：WordPress调试模式。
 *
 * 将这个值改为true，WordPress将显示所有用于开发的提示。
 * 强烈建议插件开发者在开发环境中启用WP_DEBUG。
 */
define('WP_DEBUG', false);

/**
 * zh_CN本地化设置：启用ICP备案号显示
 *
 * 可在设置→常规中修改。
 * 如需禁用，请移除或注释掉本行。
 */
define('WP_ZH_CN_ICP_NUM', true);

/* 好了！请不要再继续编辑。请保存本文件。使用愉快！ */

/** WordPress目录的绝对路径。 */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** 设置WordPress变量和包含文件。 */
require_once(ABSPATH . 'wp-settings.php');

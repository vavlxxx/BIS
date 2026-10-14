<?php
function add_action(...$a) {}
function add_filter(...$a) {}
function get_option($k,$d='') { return $d; }
function bis_get_runtime_setting($k,$d='') { return $d; }
require __DIR__.'/../wp-theme/inc/gallery-s3.php';
$config=['endpoint'=>'https://s3.example.com','region'=>'us-east-1','bucket'=>'photos','access_key'=>'example','secret_key'=>'secret','public_url'=>'https://cdn.example.com'];
$url=bis_s3_presign($config,'PUT','gallery/a b.jpg',900,1600000000);
parse_str(parse_url($url,PHP_URL_QUERY),$q);
if (parse_url($url,PHP_URL_PATH)!=='/photos/gallery/a%20b.jpg' || $q['X-Amz-Expires']!=='900' || $q['X-Amz-SignedHeaders']!=='host' || strlen($q['X-Amz-Signature'])!==64) throw new RuntimeException('Invalid presign');
if ($q['X-Amz-Signature']!=='1c2e8a80e1e1dbbbb0b1c9e7dc88eb5417f4a205551e6235062dba130503101d') throw new RuntimeException('Independent Python HMAC vector mismatch');
if ($url!==bis_s3_presign($config,'PUT','gallery/a b.jpg',900,1600000000)) throw new RuntimeException('Not deterministic');
if ($url===bis_s3_presign($config,'GET','gallery/a b.jpg',900,1600000000)) throw new RuntimeException('Method not signed');
if (bis_gallery_file_kind('x.mp4','video/mp4')!=='video' || bis_gallery_file_kind('x.svg','image/svg+xml')!=='' || bis_gallery_file_kind('x.jpg','application/x-php')!=='') throw new RuntimeException('Unsafe file accepted');
echo "S3 signing and file-type tests passed\n";

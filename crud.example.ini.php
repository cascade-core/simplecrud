; <?php exit(); __halt_compiler(); ?>
;
; Example configuration of SimpleCrud block storage
;
; Copy this file to app/, modify as you wish and point SimpleCrud block storage
; in app/core.ini.php to it.
;
;

; this will generate blocks like 'block/prefix/read'
[block/prefix]
name = "Human-friendly name"
description = "Not too long description of this entity. Use whole sentences here."
driver_class = "\SimpleCrud\DibiDriver"
db_table = "some_table"
primary_key = "id"


; vim:filetype=dosini:


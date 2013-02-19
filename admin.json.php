{
	"_": "<?php printf('_%c%c}%c',34,10,10);__halt_compiler();?>",
	"main_menu": {
		"simple_crud": {
			"title": "Simple CRUD",
			"children": {
				"types": {
					"title": "Types",
					"link": "/admin/simple-crud/type",
					"weight": 20
				}
			}
		}
	},
	"routes": {
		"/simple-crud/type": {
			"title": "Entity types",
			"block": "simplecrud/admin/type/index",
			"connections": {
			}
		}
	}
}


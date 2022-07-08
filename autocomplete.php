<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Documento sin título</title>
<script src="js/jquery-3.1.1.min.js"></script>
<link href="css/plugins/easyautocomplete/easy-autocomplete.min.css" rel="stylesheet">
<link href="css/plugins/easyautocomplete/easy-autocomplete.themes.min.css" rel="stylesheet">
<script src="js/plugins/easyautocomplete/jquery.easy-autocomplete.min.js"></script>
</head>

<body>
<input name="ClienteLlamada" type="hidden" id="ClienteLlamada" value="">
<div class="easy-autocomplete eac-square" style="width: 350px;"><input id="NombreClienteLlamada" placeholder="Square theme" autocomplete="off"><div class="easy-autocomplete-container" id="eac-container-square"><ul style="display: none;"></ul></div></div>
<script>
	 $(document).ready(function(){
		 var options = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=7&id="+phrase;
			},

			getValue: "NombreCliente",
			theme: "square",
			 template: {
				type: "description",
				fields: {
					description: "CodigoCliente"
				}
			},
			requestDelay: 400,
			list: {
				match: {
					enabled: true
				},
				onSelectItemEvent: function() {
					var value = $("#NombreClienteLlamada").getSelectedItemData().CodigoCliente;
					$("#ClienteLlamada").val(value).trigger("change");
				}
			}
		};

		$("#NombreClienteLlamada").easyAutocomplete(options);
	});
</script>
</body>
</html>
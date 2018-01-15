<link rel="stylesheet" type="text/css" href="./assets/addons/search_it/plugins/autocomplete/jquery.suggest.css" media="screen" />
<script type="text/javascript" src="./assets/addons/search_it/plugins/autocomplete/jquery.suggest.js"></script>

<script type="text/javascript">
  jQuery(document).ready(function() {
    jQuery(function() {
      jQuery(".search_it-form input[name=search]").suggest("###SERVER###<?php echo rex_getUrl('###HANDLEID###', rex_clang::getCurrentId());?>?rnd=" + Math.random(), {
        onSelect: function(event, ui) {
          $('.search_it-form').submit();
          return false;
        }
      });
      
    });
  });
</script>

<html>
  <head>
    <title>My todo list</title>
    <style type="text/css" media="screen">
    /* <![CDATA[ */
      #todolist li {
        list-style: none;
      }

      #debug {
        margin: 1em;
        padding: 0.5em;
        color: #ccc;
      }
    /* ]]> */
    </style>
    <script src="<?= dirname($controller->url_for('')) ?>/javascripts/prototype.js" type="text/javascript" language="javascript" charset="utf-8" ></script>
    <script src="<?= dirname($controller->url_for('')) ?>/javascripts/scriptaculous.js" type="text/javascript" language="javascript" charset="utf-8" ></script>
  </head>

  <body>

    <?= $content_for_layout ?>

    <div id="debug"></div>
    <script type="text/javascript">
      //<![CDATA[
      Ajax.Responders.register({
        onCreate:
          function(request, transport) {
            new Insertion.Bottom('debug', '<p><strong>[' + new Date().toString() + '] accessing ' + request.url + '</strong></p>')
          },

        onComplete:
          function(request, transport) {
            new Insertion.Bottom('debug', '<p><strong>http status: ' + transport.status + '</strong></p>' + '<pre>' + transport.responseText.escapeHTML() + '</pre>')
          }});
      //]]>
    </script>

  </body>
</html>

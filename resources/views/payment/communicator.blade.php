<!DOCTYPE html>
<html>
<head>
    <title>IFrame Communicator</title>
    <script type="text/javascript">
        function callParentFunction(str) {
            if (str && str.length > 0 && window.parent && window.parent.parent) {
                // FIX: Use '*' to allow the message to be sent to the parent regardless of origin mismatch
                // Or use window.location.origin to be safer
                window.parent.parent.postMessage(str, "*"); 
            }
        }

        function receiveMessage(event) {
            if (event && event.data) {
                callParentFunction(event.data);
            }
        }

        if (window.addEventListener) {
            window.addEventListener("message", receiveMessage, false);
        } else if (window.attachEvent) {
            window.attachEvent("onmessage", receiveMessage);
        }
    </script>
</head>
<body></body>
</html>
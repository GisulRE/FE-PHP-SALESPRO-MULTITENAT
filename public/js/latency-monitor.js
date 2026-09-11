/**
 * Sistema de Monitoreo de Rendimiento y Latencia Servidor-Cliente (Tiempo Real)
 * - Ping periódico cada 30 segundos a /check-latency
 * - Intercepción universal de Fetch y jQuery AJAX
 * - Lectura del encabezado X-Response-Time (tiempo de procesamiento del servidor)
 * - Ventana de decaimiento (15s) para peticiones AJAX lentas
 * - Actualización visual reactiva de widgets e indicadores de estado
 */
(function (window, document) {
    'use strict';

    // Configuración general
    var CONFIG = {
        pingUrl: '/check-latency',
        pingIntervalMs: 30000,     // 30 segundos
        decayWindowMs: 15000,      // 15 segundos de decaimiento para AJAX lentos
        timeoutMs: 10000,          // 10 segundos timeout para ping
        thresholds: {
            optimal: 500,          // < 500ms = Óptimo (Verde)
            acceptable: 1500       // 500ms - 1500ms = Aceptable (Amarillo), > 1500ms = Lento (Rojo)
        },
        ignoreUrls: [
            '/check-latency',
            '/api/ping',
            'check-latency',
            'api/ping'
        ]
    };

    // Estado reactivo del monitor
    var state = {
        lastPingRtt: null,
        lastPingServerMs: null,
        lastAjaxRtt: null,
        lastAjaxServerMs: null,
        lastAjaxTimestamp: 0,
        isSlowAjax: false,
        isOffline: !navigator.onLine,
        isMeasuring: false,
        pingTimer: null,
        decayTimer: null
    };

    /**
     * Verifica si una URL corresponde a una petición de diagnóstico interno que deba ignorarse
     */
    function isIgnoredUrl(url) {
        if (!url || typeof url !== 'string') return false;
        for (var i = 0; i < CONFIG.ignoreUrls.length; i++) {
            if (url.indexOf(CONFIG.ignoreUrls[i]) !== -1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Extrae el valor numérico en ms de la cabecera X-Response-Time (ej: "123.45ms" -> 123)
     */
    function parseServerTime(headerVal) {
        if (!headerVal) return null;
        var cleaned = ('' + headerVal).replace(/ms/gi, '').trim();
        var parsed = parseFloat(cleaned);
        return isNaN(parsed) ? null : Math.round(parsed);
    }

    /**
     * Determina la latencia efectiva a mostrar teniendo en cuenta la ventana de decaimiento
     */
    function getEffectiveLatency() {
        if (state.isOffline) return null;

        var now = Date.now();
        var hasRecentAjax = state.lastAjaxTimestamp > 0 && (now - state.lastAjaxTimestamp) < CONFIG.decayWindowMs;

        // Si hubo una petición AJAX reciente, tomamos la más representativa
        if (hasRecentAjax && state.lastAjaxRtt !== null) {
            // Si la última petición fue lenta, prevalece durante la ventana de decaimiento
            if (state.lastAjaxRtt >= CONFIG.thresholds.acceptable) {
                return state.lastAjaxRtt;
            }
            // Si no, promediamos o tomamos la más reciente entre ping y ajax
            return state.lastAjaxRtt;
        }

        return state.lastPingRtt;
    }

    /**
     * Determina el nivel de estado según la latencia
     */
    function getLatencyLevel(ms) {
        if (state.isOffline || ms === null || isNaN(ms)) {
            return 'offline';
        }
        if (ms < CONFIG.thresholds.optimal) {
            return 'optimal';
        }
        if (ms <= CONFIG.thresholds.acceptable) {
            return 'acceptable';
        }
        return 'slow';
    }

    /**
     * Actualiza todos los componentes de la interfaz de usuario en el DOM
     */
    function updateUI() {
        var effectiveMs = getEffectiveLatency();
        var level = getLatencyLevel(effectiveMs);

        // Estilos e información por nivel
        var styles = {
            optimal: {
                color: '#28a745',
                dotClass: 'latency-dot status-green',
                badgeClass: 'badge badge-success',
                badgeText: 'Óptimo',
                statusText: 'Excelente (<500ms)',
                statusClass: 'text-success',
                showWarning: false
            },
            acceptable: {
                color: '#ffc107',
                dotClass: 'latency-dot status-yellow',
                badgeClass: 'badge badge-warning text-dark',
                badgeText: 'Aceptable',
                statusText: 'Aceptable (500-1500ms)',
                statusClass: 'text-warning',
                showWarning: false
            },
            slow: {
                color: '#dc3545',
                dotClass: 'latency-dot status-red',
                badgeClass: 'badge badge-danger',
                badgeText: 'Lento',
                statusText: 'Lento (>1500ms)',
                statusClass: 'text-danger',
                showWarning: true
            },
            offline: {
                color: '#6c757d',
                dotClass: 'latency-dot status-offline',
                badgeClass: 'badge badge-secondary',
                badgeText: 'Sin Conexión',
                statusText: 'Desconectado',
                statusClass: 'text-muted',
                showWarning: false
            }
        };

        var currentStyle = styles[level];

        // 1. Punto indicador (dot)
        var dots = document.querySelectorAll('#latency-indicator-dot, .latency-indicator-dot');
        dots.forEach(function (dot) {
            dot.className = currentStyle.dotClass;
            dot.style.backgroundColor = currentStyle.color;
            dot.style.boxShadow = (level === 'offline') ? 'none' : '0 0 6px ' + currentStyle.color;
        });

        // 2. Texto de latencia en la barra
        var texts = document.querySelectorAll('#latency-text, .latency-text');
        var displayText = (level === 'offline' || effectiveMs === null) ? 'Sin red' : (Math.round(effectiveMs) + ' ms');
        texts.forEach(function (el) {
            el.textContent = displayText;
        });

        // 3. Badge de estado en el dropdown
        var badges = document.querySelectorAll('#latency-status-badge, .latency-status-badge');
        badges.forEach(function (badge) {
            badge.className = currentStyle.badgeClass;
            badge.textContent = currentStyle.badgeText;
        });

        // 4. Detalle de Ping
        var pingVals = document.querySelectorAll('#latency-ping-val, .latency-ping-val');
        var pingText = (state.lastPingRtt !== null) ? (Math.round(state.lastPingRtt) + ' ms') : '-- ms';
        if (state.lastPingServerMs !== null) {
            pingText += ' (Serv: ' + state.lastPingServerMs + 'ms)';
        }
        pingVals.forEach(function (el) {
            el.textContent = pingText;
        });

        // 5. Detalle de Última Petición AJAX
        var ajaxVals = document.querySelectorAll('#latency-last-ajax, .latency-last-ajax');
        var ajaxText = '-- ms';
        if (state.lastAjaxRtt !== null) {
            ajaxText = Math.round(state.lastAjaxRtt) + ' ms';
            if (state.lastAjaxServerMs !== null) {
                ajaxText += ' (Serv: ' + state.lastAjaxServerMs + 'ms)';
            }
        }
        ajaxVals.forEach(function (el) {
            el.textContent = ajaxText;
        });

        // 6. Rendimiento / Estado general
        var networkStatuses = document.querySelectorAll('#latency-network-status, .latency-network-status');
        networkStatuses.forEach(function (el) {
            el.className = currentStyle.statusClass;
            el.textContent = currentStyle.statusText;
        });

        // 7. Alerta de advertencia para peticiones lentas (>1500ms)
        var warnings = document.querySelectorAll('#latency-warning-msg, .latency-warning-msg');
        warnings.forEach(function (msg) {
            if (currentStyle.showWarning) {
                msg.classList.remove('d-none');
            } else {
                msg.classList.add('d-none');
            }
        });
    }

    /**
     * Programa la ventana de decaimiento tras una petición AJAX lenta
     */
    function scheduleDecay() {
        if (state.decayTimer) {
            clearTimeout(state.decayTimer);
        }
        state.decayTimer = setTimeout(function () {
            // Pasada la ventana de 15s, reevaluamos la UI para que vuelva al estado saludable
            updateUI();
        }, CONFIG.decayWindowMs + 100);
    }

    /**
     * Registra una medición de petición AJAX / Fetch
     */
    function recordAjaxMetric(rttMs, serverTimeHeader) {
        state.lastAjaxRtt = rttMs;
        state.lastAjaxServerMs = parseServerTime(serverTimeHeader);
        state.lastAjaxTimestamp = Date.now();
        state.isOffline = false;

        updateUI();
        scheduleDecay();
    }

    /**
     * Ejecuta una llamada de ping ligera a /check-latency
     */
    function executePing(callback) {
        if (state.isMeasuring) return;
        state.isMeasuring = true;

        // Feedback visual en botón de refresco
        var refreshBtns = document.querySelectorAll('#btn-refresh-latency, .btn-refresh-latency');
        refreshBtns.forEach(function (btn) {
            var icon = btn.querySelector('i');
            if (icon) icon.classList.add('fa-spin');
            btn.setAttribute('disabled', 'disabled');
        });

        var startTime = performance.now();
        var url = CONFIG.pingUrl + '?_t=' + Date.now();

        var controller = null;
        var signal = null;
        if (window.AbortController) {
            controller = new AbortController();
            signal = controller.signal;
            setTimeout(function () {
                try { controller.abort(); } catch (e) {}
            }, CONFIG.timeoutMs);
        }

        fetch(url, {
            method: 'GET',
            cache: 'no-store',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: signal
        })
        .then(function (response) {
            var endTime = performance.now();
            var rtt = Math.round(endTime - startTime);

            if (response.ok) {
                state.isOffline = false;
                state.lastPingRtt = rtt;

                var serverHeader = response.headers.get('x-response-time') || response.headers.get('X-Response-Time');
                state.lastPingServerMs = parseServerTime(serverHeader);
            } else {
                // Si la ruta devolvió error 5xx / 4xx pero hubo respuesta
                state.lastPingRtt = rtt;
            }
            updateUI();
        })
        .catch(function () {
            state.isOffline = !navigator.onLine;
            if (state.isOffline) {
                state.lastPingRtt = null;
            }
            updateUI();
        })
        .finally(function () {
            state.isMeasuring = false;

            refreshBtns.forEach(function (btn) {
                var icon = btn.querySelector('i');
                if (icon) icon.classList.remove('fa-spin');
                btn.removeAttribute('disabled');
            });

            if (typeof callback === 'function') {
                callback();
            }
        });
    }

    /**
     * Inicia el ciclo periódico de pings cada 30 segundos
     */
    function startPingInterval() {
        if (state.pingTimer) {
            clearInterval(state.pingTimer);
        }
        state.pingTimer = setInterval(function () {
            // Solo hacer ping si el documento está visible para ahorrar recursos
            if (!document.hidden) {
                executePing();
            }
        }, CONFIG.pingIntervalMs);
    }

    /**
     * Interceptor Global para window.fetch
     */
    function setupFetchInterceptor() {
        if (!window.fetch) return;

        var originalFetch = window.fetch;
        window.fetch = function () {
            var args = arguments;
            var resource = args[0];
            var url = (typeof resource === 'string') ? resource : (resource && resource.url ? resource.url : '');

            // Ignorar peticiones internas de diagnóstico
            if (isIgnoredUrl(url)) {
                return originalFetch.apply(this, args);
            }

            var start = performance.now();

            return originalFetch.apply(this, args)
                .then(function (response) {
                    var duration = Math.round(performance.now() - start);
                    var serverHeader = null;
                    if (response && response.headers && typeof response.headers.get === 'function') {
                        serverHeader = response.headers.get('x-response-time') || response.headers.get('X-Response-Time');
                    }
                    recordAjaxMetric(duration, serverHeader);
                    return response;
                })
                .catch(function (error) {
                    var duration = Math.round(performance.now() - start);
                    recordAjaxMetric(duration, null);
                    throw error;
                });
        };
    }

    /**
     * Interceptor Global para jQuery AJAX (si jQuery está disponible)
     */
    function setupJQueryInterceptor() {
        if (!window.jQuery) return;

        var $ = window.jQuery;

        $(document).ajaxSend(function (event, jqXHR, settings) {
            if (settings && isIgnoredUrl(settings.url)) return;
            settings._perfStartTime = performance.now();
        });

        $(document).ajaxComplete(function (event, jqXHR, settings) {
            if (settings && isIgnoredUrl(settings.url)) return;

            var startTime = settings ? settings._perfStartTime : null;
            if (startTime) {
                var duration = Math.round(performance.now() - startTime);
                var serverHeader = null;
                try {
                    serverHeader = jqXHR.getResponseHeader('X-Response-Time');
                } catch (e) {}
                recordAjaxMetric(duration, serverHeader);
            }
        });
    }

    /**
     * Inicialización del monitor al cargar el documento
     */
    function init() {
        // 1. Interceptar Fetch y jQuery AJAX
        setupFetchInterceptor();
        setupJQueryInterceptor();

        // 2. Si jQuery se carga asíncronamente más tarde, engancharlo
        if (!window.jQuery) {
            var checkJQuery = setInterval(function () {
                if (window.jQuery) {
                    setupJQueryInterceptor();
                    clearInterval(checkJQuery);
                }
            }, 500);
            setTimeout(function () { clearInterval(checkJQuery); }, 10000);
        }

        // 3. Event listeners de red del navegador
        window.addEventListener('online', function () {
            state.isOffline = false;
            executePing();
        });

        window.addEventListener('offline', function () {
            state.isOffline = true;
            updateUI();
        });

        // 4. Click en el botón manual "Medir Latencia Ahora"
        document.addEventListener('click', function (e) {
            var target = e.target.closest('#btn-refresh-latency, .btn-refresh-latency');
            if (target) {
                e.preventDefault();
                executePing();
            }
        });

        // 5. Si la pestaña vuelve a tener foco, ejecutar un ping fresco
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                executePing();
            }
        });

        // 6. Primer ping inmediato y arranque del temporizador de 30s
        executePing(function () {
            startPingInterval();
        });
    }

    // Arrancar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Exponer API para testing o depuración en consola
    window.LatencyMonitor = {
        refresh: executePing,
        getState: function () {
            return Object.assign({}, state);
        },
        CONFIG: CONFIG
    };

})(window, document);

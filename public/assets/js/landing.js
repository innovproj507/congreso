// Interacciones de la landing (sin dependencias). Sustituye a los componentes React del demo.
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var datos = {};
  try { datos = JSON.parse($('#datos-landing').textContent); } catch (e) {}

  // ---- Aparición al desplazarse
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -60px 0px' });
    $$('.reveal').forEach(function (el) { io.observe(el); });
  } else { $$('.reveal').forEach(function (el) { el.classList.add('is-in'); }); }

  // ---- Botón flotante «Inscríbete»: aparece al bajar y se oculta cuando ya se ven las inscripciones
  var flotante = $('#btn-flotante');
  if (flotante) {
    var vistoInsc = false;
    function pintarFlotante() {
      var mostrar = window.scrollY > 500 && !vistoInsc;
      flotante.classList.toggle('hidden', !mostrar); flotante.classList.toggle('inline-flex', mostrar);
    }
    if ('IntersectionObserver' in window) {
      var enPantalla = {}; // cada sección se registra por separado: el aviso de una no debe borrar el estado de la otra
      var ioInsc = new IntersectionObserver(function (es) {
        es.forEach(function (e) { enPantalla[e.target.id] = e.isIntersecting; });
        vistoInsc = Object.keys(enPantalla).some(function (k) { return enPantalla[k]; }); pintarFlotante();
      });
      ['#inscripciones', '#registro'].forEach(function (q) { var el = $(q); if (el) ioInsc.observe(el); });
    }
    window.addEventListener('scroll', pintarFlotante, { passive: true }); pintarFlotante();
  }

  // ---- Pestañas (data-tabs > [data-tab] / [data-panel])
  $$('[data-tabs]').forEach(function (grupo) {
    var tabs = $$('[data-tab]', grupo), paneles = $$('[data-panel]', grupo);
    tabs.forEach(function (t) {
      t.addEventListener('click', function () {
        var i = t.getAttribute('data-tab');
        tabs.forEach(function (x) { var on = x === t; x.classList.toggle('is-on', on); x.setAttribute('aria-selected', String(on)); });
        paneles.forEach(function (p) { p.classList.toggle('hidden', p.getAttribute('data-panel') !== i); });
      });
    });
  });

  // ---- Cuenta regresiva
  var fin = new Date(datos.inicio).getTime();
  var celdas = { dias: $('[data-cuenta=dias]'), horas: $('[data-cuenta=horas]'), min: $('[data-cuenta=min]'), seg: $('[data-cuenta=seg]') };
  function tick() {
    var s = Math.max(0, Math.floor((fin - Date.now()) / 1000));
    if (isNaN(s)) return;
    celdas.dias.textContent = Math.floor(s / 86400);
    celdas.horas.textContent = Math.floor((s % 86400) / 3600);
    celdas.min.textContent = Math.floor((s % 3600) / 60);
    celdas.seg.textContent = s % 60;
  }
  if (celdas.dias) { tick(); setInterval(tick, 1000); }

  // ---- Inclinación 3D del arte del hero
  var wrap = $('#arte-wrap'), arte = $('#arte');
  if (wrap && arte && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    wrap.addEventListener('mousemove', function (e) {
      var r = wrap.getBoundingClientRect();
      var ry = ((e.clientX - r.left) / r.width - 0.5) * 24, rx = -((e.clientY - r.top) / r.height - 0.5) * 24;
      arte.style.transform = 'rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)';
    });
    wrap.addEventListener('mouseleave', function () { arte.style.transform = ''; });
    arte.style.transition = 'transform .25s ease-out, opacity .6s ease';
  }

  // ---- Galería dinámica: Visor animado (crossfade + Ken Burns) + Mosaico filtrable + Lightbox
  var lb = $('#lightbox');
  if (lb) {
    var G = { fotos: [], video: null };
    try { G = JSON.parse($('#datos-galeria').textContent); } catch (e) {}
    var n = G.fotos.length, idx = 0, modoVideo = false, abridor = null;
    var img = $('#lb-img'), vid = $('#lb-video'), tit = $('#lb-titulo'), cont = $('#lb-contador');
    var btnPrev = $('#lb-prev'), btnNext = $('#lb-next'), thumbsBox = $('#lb-thumbs');
    var lbThumbs = $$('[data-lb-thumb]');
    var gridItems = $$('.galeria-item');

    function dosDigitos(v) { return v < 10 ? '0' + v : String(v); }
    function textoFoto(f, i) { return f.titulo || ('Congreso Nacional de Pediatría 2026 · Momento ' + (i + 1)); }
    function precargar(i) { if (n > 1) { var im = new Image(); im.src = G.fotos[(i + n) % n].src; } }

    // ---- Escenario principal (presentación automática con fundido cruzado)
    var escenario = $('#visor-escenario'),
        capaA = $('#visor-img-a'),
        capaB = $('#visor-img-b'),
        fondoVisor = $('#visor-fondo'),
        visorNum = $('#visor-num'),
        visorCap = $('#visor-caption'),
        visorBarra = $('#visor-barra'),
        visorEstado = $('#visor-estado'),
        visorPunto = $('#visor-punto'),
        btnVisorPrev = $('#visor-prev'),
        btnVisorNext = $('#visor-next'),
        btnVisorPausa = $('#visor-pausa'),
        btnVisorAmpliar = $('#visor-ampliar');

    var visorIdx = 0,
        usandoA = true,
        pausadoUsuario = false,
        pausadoHover = false,
        DURACION_MS = 4200,
        inicioCiclo = Date.now(),
        rafId = null;

    function ajustarModoCapa(capa, f) {
      var esVert = f.orient === 'vertical';
      capa.classList.toggle('object-contain', esVert);
      capa.classList.toggle('object-cover', !esVert);
    }

    function irAVisor(nuevoIdx) {
      if (!n || !capaA || !capaB) return;
      visorIdx = (nuevoIdx + n) % n;
      var f = G.fotos[visorIdx];
      var entrante = usandoA ? capaB : capaA;
      var saliente = usandoA ? capaA : capaB;
      usandoA = !usandoA;

      ajustarModoCapa(entrante, f);
      entrante.src = f.src;
      entrante.alt = f.alt;
      entrante.classList.add('is-activa');
      saliente.classList.remove('is-activa');

      if (fondoVisor) fondoVisor.src = f.thumb || f.src;
      if (visorNum) visorNum.textContent = dosDigitos(visorIdx + 1) + ' / ' + dosDigitos(n);
      if (visorCap) visorCap.textContent = textoFoto(f, visorIdx);

      gridItems.forEach(function (b, k) { b.classList.toggle('is-activa', k === visorIdx); });
      precargar(visorIdx + 1);
      inicioCiclo = Date.now();
      if (visorBarra) visorBarra.style.width = '0%';
    }

    function cicloVisor() {
      var detenido = pausadoUsuario || pausadoHover || !lb.classList.contains('hidden');
      if (detenido) {
        inicioCiclo = Date.now();
      } else if (n > 1) {
        var trans = Date.now() - inicioCiclo;
        var pct = Math.min(100, (trans / DURACION_MS) * 100);
        if (visorBarra) visorBarra.style.width = pct + '%';
        if (trans >= DURACION_MS) {
          irAVisor(visorIdx + 1);
        }
      }
      rafId = requestAnimationFrame(cicloVisor);
    }

    if (escenario && n > 0) {
      ajustarModoCapa(capaA, G.fotos[0]);
      rafId = requestAnimationFrame(cicloVisor);

      escenario.addEventListener('mouseenter', function () { pausadoHover = true; });
      escenario.addEventListener('mouseleave', function () { pausadoHover = false; });
      escenario.addEventListener('click', function () { abrir('foto', visorIdx, escenario); });

      if (btnVisorAmpliar) {
        btnVisorAmpliar.addEventListener('click', function (e) { e.stopPropagation(); abrir('foto', visorIdx, btnVisorAmpliar); });
      }
      if (btnVisorPrev) {
        btnVisorPrev.addEventListener('click', function (e) { e.stopPropagation(); irAVisor(visorIdx - 1); });
      }
      if (btnVisorNext) {
        btnVisorNext.addEventListener('click', function (e) { e.stopPropagation(); irAVisor(visorIdx + 1); });
      }
      if (btnVisorPausa) {
        btnVisorPausa.addEventListener('click', function (e) {
          e.stopPropagation();
          pausadoUsuario = !pausadoUsuario;
          btnVisorPausa.setAttribute('aria-pressed', String(pausadoUsuario));
          btnVisorPausa.setAttribute('aria-label', pausadoUsuario ? 'Reanudar presentación' : 'Pausar presentación');
          var icPausa = $('[data-ic=pausa]', btnVisorPausa), icPlay = $('[data-ic=play]', btnVisorPausa);
          if (icPausa) icPausa.classList.toggle('hidden', pausadoUsuario);
          if (icPlay) icPlay.classList.toggle('hidden', !pausadoUsuario);
          if (visorEstado) visorEstado.textContent = pausadoUsuario ? 'En pausa' : 'Presentación automática';
          if (visorPunto) visorPunto.classList.toggle('animate-pulse', !pausadoUsuario);
        });
      }
    }

    // ---- Filtros del mosaico y botón "Ver todas"
    var filtroActual = 'todas',
        expandido = false,
        btnMas = $('#galeria-mas-btn'),
        wrapMas = $('#galeria-mas-wrap'),
        txtMas = $('#galeria-mas-txt');

    function actualizarMosaico() {
      gridItems.forEach(function (el) {
        var orient = el.getAttribute('data-orient');
        var esExtra = el.getAttribute('data-extra') === '1';
        var pasaFiltro = filtroActual === 'todas' || orient === filtroActual;
        var mostrar = pasaFiltro && (filtroActual !== 'todas' || expandido || !esExtra);
        el.classList.toggle('hidden', !mostrar);
      });
      if (wrapMas) {
        wrapMas.classList.toggle('hidden', filtroActual !== 'todas');
      }
    }

    $$('[data-filtro]').forEach(function (b) {
      b.addEventListener('click', function () {
        filtroActual = b.getAttribute('data-filtro');
        $$('[data-filtro]').forEach(function (x) { x.classList.toggle('is-on', x === b); });
        actualizarMosaico();
      });
    });

    if (btnMas) {
      var textoOriginalMas = txtMas ? txtMas.textContent : '';
      btnMas.addEventListener('click', function () {
        expandido = !expandido;
        btnMas.setAttribute('data-expandido', expandido ? '1' : '0');
        if (txtMas) txtMas.textContent = expandido ? 'Mostrar menos fotografías' : textoOriginalMas;
        actualizarMosaico();
      });
    }

    // ---- Lightbox (pantalla completa)
    function mostrarFoto(i) {
      idx = (i + n) % n; var f = G.fotos[idx];
      vid.pause(); vid.classList.add('hidden'); img.classList.remove('hidden');
      if (thumbsBox) thumbsBox.classList.remove('hidden');
      img.src = f.src; img.alt = f.alt; tit.textContent = textoFoto(f, idx);
      cont.textContent = dosDigitos(idx + 1) + ' / ' + dosDigitos(n);
      lbThumbs.forEach(function (t, k) {
        var activo = k === idx;
        t.classList.toggle('is-on', activo);
        if (activo && t.scrollIntoView) t.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
      });
      precargar(idx + 1); precargar(idx - 1);
    }

    function mostrarVideo() {
      var v = G.video; if (!v) return;
      modoVideo = true; img.classList.add('hidden'); vid.classList.remove('hidden');
      if (thumbsBox) thumbsBox.classList.add('hidden');
      if (vid.getAttribute('data-src') !== v.src) {
        vid.innerHTML = ''; var so = document.createElement('source'); so.src = v.src; so.type = v.tipo; vid.appendChild(so);
        vid.setAttribute('data-src', v.src); if (v.poster) vid.poster = v.poster; vid.load();
      }
      tit.textContent = v.titulo; cont.textContent = 'VIDEO OFICIAL';
      btnPrev.classList.add('hidden'); btnNext.classList.add('hidden');
      var p = vid.play(); if (p && p.catch) p.catch(function () {});
    }

    function abrir(modo, i, desde) {
      abridor = desde || null; modoVideo = false;
      btnPrev.classList.toggle('hidden', n < 2); btnNext.classList.toggle('hidden', n < 2);
      lb.classList.remove('hidden'); lb.classList.add('flex'); document.body.style.overflow = 'hidden';
      if (modo === 'video') mostrarVideo(); else mostrarFoto(i);
      $('#lb-cerrar').focus();
    }

    function cerrar() {
      vid.pause(); lb.classList.add('hidden'); lb.classList.remove('flex'); document.body.style.overflow = '';
      if (abridor && document.contains(abridor)) abridor.focus();
    }

    function mover(d) { if (!modoVideo && n > 1) mostrarFoto(idx + d); }

    $$('[data-foto]').forEach(function (b) {
      b.addEventListener('click', function () {
        var i = +b.getAttribute('data-foto');
        irAVisor(i);
        abrir('foto', i, b);
      });
    });
    lbThumbs.forEach(function (b) {
      b.addEventListener('click', function (e) {
        e.stopPropagation();
        mostrarFoto(+b.getAttribute('data-lb-thumb'));
      });
    });
    $$('[data-video]').forEach(function (b) { b.addEventListener('click', function () { abrir('video', 0, b); }); });
    $('#lb-cerrar').addEventListener('click', cerrar);
    btnPrev.addEventListener('click', function (e) { e.stopPropagation(); mover(-1); });
    btnNext.addEventListener('click', function (e) { e.stopPropagation(); mover(1); });
    lb.addEventListener('click', function (e) { if (e.target === lb) cerrar(); });
    document.addEventListener('keydown', function (e) {
      if (lb.classList.contains('hidden')) return;
      if (e.key === 'Escape') cerrar();
      else if (e.key === 'ArrowRight') mover(1);
      else if (e.key === 'ArrowLeft') mover(-1);
      else if (e.key === 'Tab') {
        var f = $$('button:not(.hidden), video:not(.hidden)', lb).filter(function (x) { return x.offsetParent !== null; });
        if (!f.length) return;
        var p = f.indexOf(document.activeElement);
        if (e.shiftKey && p <= 0) { e.preventDefault(); f[f.length - 1].focus(); }
        else if (!e.shiftKey && p === f.length - 1) { e.preventDefault(); f[0].focus(); }
      }
    });

    var x0 = null;
    lb.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function (e) {
      if (x0 === null) return; var dx = e.changedTouches[0].clientX - x0; x0 = null;
      if (Math.abs(dx) > 50) mover(dx < 0 ? 1 : -1);
    }, { passive: true });
  }

  // ---- Token CSRF: CodeIgniter lo regenera en cada envío y lo devuelve en la respuesta
  var csrf = ($('meta[name=csrf-token]') || {}).content || '';
  function enviarJson(url, cuerpo, senal) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(cuerpo), credentials: 'same-origin', signal: senal || undefined
    }).then(function (res) {
      return res.json().catch(function () { return { ok: false, error: 'La sesión expiró. Recarga la página e inténtalo de nuevo.' }; })
        .then(function (j) { if (j && j.csrf) csrf = j.csrf; j.status = res.status; return j; });
    });
  }

  function enviarForm(url, fd, senal) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
      body: fd, credentials: 'same-origin', signal: senal || undefined
    }).then(function (res) {
      return res.json().catch(function () { return { ok: false, error: 'La sesión expiró. Recarga la página e inténtalo de nuevo.' }; })
        .then(function (j) { if (j && j.csrf) csrf = j.csrf; j.status = res.status; return j; });
    });
  }

  // ---- Formulario de inscripción
  var form = $('#registro-form');
  if (form) {
    var periodo = datos.vigente || 'preventa';
    var titulos = { preventa: 'Preventa (Early Bird)', regular: 'Regular' };
    var rangos = { preventa: '30 de octubre de 2026 – 15 de marzo de 2027', regular: '16 de marzo de 2027 en adelante' };
    var selCat = $('#categoria'), tarifas = (datos.tarifas || {})[periodo] || [];
    tarifas.forEach(function (t) { var o = document.createElement('option'); o.value = t.categoria; o.textContent = t.categoria + ' — ' + t.precio; selCat.appendChild(o); });
    $('#tarifa-titulo').textContent = titulos[periodo]; $('#tarifa-rango').textContent = rangos[periodo];
    function precio() { var t = tarifas.filter(function (x) { return x.categoria === selCat.value; })[0]; return t ? t.precio : ''; }
    function pintarPrecio() { const el = $('#tarifa-precio'); if (el) el.textContent = precio(); }
    selCat.addEventListener('change', pintarPrecio); pintarPrecio();

    var pais = $('#pais');
    function pintarExtranjero() {
      var p = pais.value.trim().toLowerCase();
      $('#aviso-extranjero').classList.toggle('hidden', !(p && p !== 'panamá' && p !== 'panama'));
    }
    pais.addEventListener('input', pintarExtranjero); pintarExtranjero();

    // Medio de pago: tarjetas (ACH, Yappy, tarjeta) y un panel que cambia según el elegido
    var PAGOS = {
      ach: { titulo: 'Transferencia Bancaria ACH', icono: '🏦', color: '#1e3a8a', nota: 'Realiza la transferencia y adjunta el comprobante a continuación.' },
      yappy: { titulo: 'Pago con Yappy', icono: '📱', color: '#059669', nota: 'Después de pagar con Yappy, adjunta la captura de pantalla del comprobante.' },
      tarjeta: { titulo: 'Tarjeta de Crédito', icono: '💳', color: '#7c3aed', nota: 'Ingresa los datos de tu tarjeta en el formulario seguro a continuación.' }
    };
    var tarjetasPago = $$('.pago-card', form), metodo = '', tokenTarjeta = '';
    var zona = $('#zona-comprobante'), inputArchivo = form.elements.comprobante;
    var ZONA_VACIA = '<div style="font-size:2.2rem;margin-bottom:.5rem">📎</div><p style="color:#374151;font-weight:600;font-size:.875rem;margin:0 0 .25rem">Arrastra aquí o haz clic para seleccionar</p><p style="color:#9ca3af;font-size:.75rem;margin:0">JPG, PNG, WebP o PDF — máximo 10 MB</p>';
    function pintarArchivo() {
      var a = inputArchivo.files && inputArchivo.files[0];
      zona.style.borderStyle = a ? 'solid' : 'dashed'; zona.style.borderColor = a ? '#10b981' : '#d1d5db'; zona.style.background = a ? '#f0fdf4' : '#fafafa';
      if (!a) { zona.innerHTML = ZONA_VACIA; return; }
      zona.innerHTML = '<div style="font-size:2.2rem;margin-bottom:.5rem"></div><p style="color:#065f46;font-weight:700;font-size:.875rem;margin:0 0 .25rem"></p><p style="color:#6b7280;font-size:.75rem;margin:0"><span></span> — <button type="button" style="color:#dc2626;text-decoration:underline">Eliminar</button></p>';
      zona.children[0].textContent = a.type === 'application/pdf' ? '📄' : '🖼️';
      zona.children[1].textContent = '✅ ' + a.name;
      zona.children[2].firstChild.textContent = (a.size / 1024 / 1024).toFixed(2) + ' MB';
      zona.querySelector('button').addEventListener('click', function (e) { e.stopPropagation(); inputArchivo.value = ''; pintarArchivo(); marcar('comprobante', ''); });
    }
    function elegirArchivo(a) {
      if (!a) return;
      var dt = new DataTransfer(); dt.items.add(a); inputArchivo.files = dt.files; pintarArchivo(); validarComprobante();
    }
    zona.addEventListener('click', function () { inputArchivo.click(); });
    zona.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); inputArchivo.click(); } });
    zona.addEventListener('dragover', function (e) { e.preventDefault(); zona.classList.add('arrastrando'); });
    zona.addEventListener('dragleave', function () { zona.classList.remove('arrastrando'); });
    zona.addEventListener('drop', function (e) { e.preventDefault(); zona.classList.remove('arrastrando'); elegirArchivo(e.dataTransfer.files[0]); });
    inputArchivo.addEventListener('change', function () { pintarArchivo(); validarComprobante(); });
    pintarArchivo();

    // Widget de tarjeta del banco (CROEM): necesita jQuery, que solo se carga si la persona elige tarjeta
    var cont = $('#creditcard-container'), estadoTarjeta = $('#tarjeta-estado'), cvvCaja = $('#cvv-caja');
    function avisoTarjeta(ok, texto) {
      estadoTarjeta.style.display = texto ? 'block' : 'none'; estadoTarjeta.textContent = texto || '';
      estadoTarjeta.style.background = ok ? '#f0fdf4' : '#fef2f2'; estadoTarjeta.style.color = ok ? '#166534' : '#991b1b';
    }
    function sinToken() { tokenTarjeta = ''; form.elements.account_token.value = ''; cvvCaja.classList.add('hidden'); form.elements.cvv.value = ''; textoBoton(); }
    function conJquery(fn) {
      if (window.jQuery) return fn();
      var sc = document.createElement('script'); sc.src = datos.jquery; sc.onload = fn;
      sc.onerror = function () { cont.innerHTML = '<p style="padding:1.5rem;color:#991b1b;text-align:center">No se pudo cargar la pasarela de pago. Recarga la página.</p>'; };
      document.head.appendChild(sc);
    }
    function cargarTarjeta() {
      sinToken(); avisoTarjeta(false, '');
      if (!datos.croem) { cont.innerHTML = '<p style="padding:1.5rem;color:#991b1b;text-align:center;font-weight:600">El pago con tarjeta no está disponible por ahora. Elige otro medio de pago.</p>'; return; }
      cont.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;padding:3rem 1rem;gap:1rem;text-align:center"><div style="width:44px;height:44px;border:4px solid #e5e7eb;border-top-color:#1e3a8a;border-radius:50%;animation:pago-giro .8s linear infinite"></div><p style="color:#374151;font-weight:600;margin:0;font-size:.875rem">Cargando pasarela de pago segura…</p><p style="color:#9ca3af;font-size:.75rem;margin:0">Conexión cifrada • Banco Nacional de Panamá</p></div>';
      window.SaveCreditCard_SuccessCallback = function (r) {
        var t = r && r.TokenDetails && r.TokenDetails.AccountToken;
        if (!t) { avisoTarjeta(false, '❌ No se pudo validar la tarjeta.'); sinToken(); return; }
        tokenTarjeta = t; form.elements.account_token.value = t; marcar('account_token', '');
        avisoTarjeta(true, '✅ Tarjeta validada — ingresa tu CVV para confirmar el pago'); cvvCaja.classList.remove('hidden'); form.elements.cvv.focus(); textoBoton();
      };
      window.SaveCreditCard_FailureCallback = function (r) { avisoTarjeta(false, '❌ Error: ' + ((r && r.ResponseDescription) || 'Error al validar la tarjeta')); sinToken(); };
      window.SaveCreditCard_CancelCallback = function () { avisoTarjeta(false, ''); sinToken(); };
      conJquery(function () {
        window.jQuery.ajax({
          type: 'GET', url: datos.croem.widget, data: { APIKey: datos.croem.apiKey, Token: '', Culture: 'es' },
          success: function (html) { window.jQuery(cont).html(html); },
          error: function () { cont.innerHTML = '<p style="padding:1.5rem;color:#991b1b;text-align:center">No se pudo conectar con la pasarela de pago segura. Elige otro medio o inténtalo más tarde.</p>'; }
        });
      });
    }
    function quitarTarjeta() {
      if (window.jQuery) window.jQuery(cont).empty();
      cont.innerHTML = ''; avisoTarjeta(false, ''); sinToken();
      delete window.SaveCreditCard_SuccessCallback; delete window.SaveCreditCard_FailureCallback; delete window.SaveCreditCard_CancelCallback;
    }

    function textoBoton() {
      var t = 'Enviar inscripción', cvv = form.elements.cvv.value;
      if (metodo === 'tarjeta') t = !tokenTarjeta ? 'Valida tu tarjeta para continuar' : !/^[0-9]{3,4}$/.test(cvv) ? 'Ingresa el CVV para finalizar' : 'Pagar y completar inscripción 💳';
      else if (metodo === 'ach' || metodo === 'yappy') t = 'Completar inscripción ✉️';
      TEXTO_BOTON = t; if (!boton.disabled) txtBoton.textContent = t;
    }
    var TEXTO_BOTON = 'Enviar inscripción';
    function pintarPagos() {
      var info = PAGOS[metodo], det = $('#pago-detalle'), pide = metodo === 'ach' || metodo === 'yappy';
      tarjetasPago.forEach(function (c) { c.classList.toggle('is-on', c.getAttribute('data-metodo') === metodo); });
      det.classList.toggle('hidden', !info);
      if (info) {
        $('#pd-icono').textContent = info.icono; $('#pd-icono').style.background = info.color + '15';
        $('#pd-titulo').textContent = info.titulo; $('#pd-sub').style.color = info.color;
        var n = $('#pago-instrucciones'); n.textContent = info.nota; n.style.borderLeftColor = info.color;
        $$('[data-detalle]', det).forEach(function (d) { d.classList.toggle('hidden', d.getAttribute('data-detalle') !== metodo); });
      }
      $('#bloque-comprobante').classList.toggle('hidden', !pide);
      $('#bloque-tarjeta').classList.toggle('hidden', metodo !== 'tarjeta');
      if (!pide) { inputArchivo.value = ''; pintarArchivo(); form.elements.referencia_pago.value = ''; marcar('comprobante', ''); marcar('referencia_pago', ''); }
      if (metodo === 'tarjeta') cargarTarjeta(); else quitarTarjeta();
      textoBoton();
    }
    tarjetasPago.forEach(function (c) {
      c.addEventListener('click', function () {
        if (metodo === c.getAttribute('data-metodo')) return;
        metodo = c.getAttribute('data-metodo'); form.elements.pago.value = c.getAttribute('data-pago'); marcar('pago', ''); pintarPagos();
      });
    });

    // ---- Validación por campo (mensajes junto al campo, en español)
    var reglas = {
      nombre: function (v) { return v.length < 2 ? 'Escribe tu nombre.' : ''; },
      apellido: function (v) { return v.length < 2 ? 'Escribe tu apellido.' : ''; },
      documento: function (v) {
        if (!v) return 'Escribe tu cédula o pasaporte.';
        if (v.length < 4) return 'Revisa tu cédula o pasaporte: es muy corta.';
        return /^[A-Za-z0-9\-\. ]+$/.test(v) ? '' : 'Solo letras, números y guiones.';
      },
      institucion: function (v) { return v.length < 2 ? 'Escribe tu institución o centro de trabajo.' : ''; },
      pais: function (v) { return v.length < 2 ? 'Indica tu país de residencia.' : ''; },
      correo: function (v) {
        if (!v) return 'Escribe tu correo electrónico.';
        return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) ? '' : 'Revisa el correo: debe verse así, nombre@dominio.com.';
      },
      telefono: function (v) {
        if (!v) return 'Escribe tu teléfono o WhatsApp.';
        if (!/^[0-9+\-\(\) ]+$/.test(v)) return 'Usa solo números, espacios, + y guiones.';
        return v.replace(/\D/g, '').length >= 7 ? '' : 'El teléfono parece incompleto.';
      }
    };
    function campoEl(n) { return form.elements[n]; }
    function msgEl(n) { return $('#err-' + n); }
    function marcar(n, texto) {
      var el = campoEl(n), m = msgEl(n);
      if (!el || !m) return;
      el.classList.toggle('campo-error', !!texto); el.setAttribute('aria-invalid', texto ? 'true' : 'false');
      m.textContent = texto || ''; m.hidden = !texto;
    }
    function validar(n) {
      var el = campoEl(n), v = (el.value || '').trim();
      var t = reglas[n] ? reglas[n](v) : '';
      marcar(n, t); return !t;
    }
    Object.keys(reglas).forEach(function (n) {
      var el = campoEl(n);
      el.addEventListener('blur', function () { if (el.value.trim() !== '' || el.classList.contains('campo-error')) validar(n); });
      el.addEventListener('input', function () { if (el.classList.contains('campo-error')) validar(n); });
    });
    function validarComprobante() {
      var a = inputArchivo.files && inputArchivo.files[0], t = '';
      if (!a) t = 'Adjunta el comprobante de tu pago.';
      else if (!/^(image\/(jpeg|png|webp)|application\/pdf)$/.test(a.type)) t = 'Formato no permitido. Usa JPG, PNG, WebP o PDF.';
      else if (a.size > 10 * 1024 * 1024) t = 'El comprobante no puede superar los 10 MB.';
      marcar('comprobante', t); return !t;
    }
    function validarPago() {
      if (!metodo) { marcar('pago', 'Elige cómo vas a pagar.'); return false; }
      var ok = true;
      if (metodo === 'ach' || metodo === 'yappy') {
        var ref = form.elements.referencia_pago.value.trim();
        marcar('referencia_pago', ref ? '' : 'Escribe el número de referencia o confirmación de tu pago.'); ok = !!ref;
        ok = validarComprobante() && ok;
      } else if (metodo === 'tarjeta') {
        marcar('account_token', tokenTarjeta ? '' : 'Valida tu tarjeta antes de enviar.'); ok = !!tokenTarjeta;
        var okCvv = /^[0-9]{3,4}$/.test(form.elements.cvv.value);
        if (tokenTarjeta) marcar('cvv', okCvv ? '' : 'Escribe el CVV de tu tarjeta (3 o 4 dígitos).');
        ok = ok && okCvv;
      }
      return ok;
    }
    form.elements.referencia_pago.addEventListener('input', function () { if (this.classList.contains('campo-error')) validarPago(); });
    form.elements.cvv.addEventListener('input', function () { this.value = this.value.replace(/[^0-9]/g, ''); textoBoton(); if (this.classList.contains('campo-error')) validarPago(); });
    var acepta = campoEl('acepta');
    acepta.addEventListener('change', function () { marcar('acepta', acepta.checked ? '' : 'Debes aceptar para poder enviar tu inscripción.'); });

    // Sugerencia ante errores típicos de escritura del correo (gmial.com → gmail.com)
    var dominios = { 'gmial.com': 'gmail.com', 'gmai.com': 'gmail.com', 'gamil.com': 'gmail.com', 'gmail.co': 'gmail.com', 'gmail.con': 'gmail.com', 'hotmial.com': 'hotmail.com', 'hotmai.com': 'hotmail.com', 'hotmail.con': 'hotmail.com', 'outlok.com': 'outlook.com', 'outlook.con': 'outlook.com', 'yaho.com': 'yahoo.com', 'yahoo.con': 'yahoo.com' };
    var sug = $('#sugerencia-correo');
    campoEl('correo').addEventListener('blur', function () {
      var v = this.value.trim().toLowerCase(), i = v.lastIndexOf('@'), d = i > 0 ? v.slice(i + 1) : '';
      if (d && dominios[d]) { sug.textContent = '¿Quisiste decir ' + v.slice(0, i) + '@' + dominios[d] + '?'; sug.setAttribute('data-valor', v.slice(0, i) + '@' + dominios[d]); sug.classList.remove('hidden'); }
      else sug.classList.add('hidden');
    });
    sug.addEventListener('click', function () { var c = campoEl('correo'); c.value = sug.getAttribute('data-valor'); sug.classList.add('hidden'); validar('correo'); });

    // ---- Envío
    var err = $('#registro-error'), boton = $('#registro-btn'), spin = $('#registro-spin'), txtBoton = $('#registro-btn-texto');
    function errorGeneral(m) { err.textContent = m; err.classList.remove('hidden'); err.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    function enfocarPrimerError() {
      var p = form.querySelector('.campo-error'); if (p) { p.focus(); p.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    }
    function enviando(si) { boton.disabled = si; spin.classList.toggle('hidden', !si); txtBoton.textContent = si ? (metodo === 'tarjeta' ? 'Procesando pago…' : 'Enviando…') : TEXTO_BOTON; }

    form.addEventListener('submit', function (e) {
      e.preventDefault(); err.classList.add('hidden');
      var ok = Object.keys(reglas).map(validar).every(Boolean);
      if (!validarPago()) ok = false;
      if (!acepta.checked) { marcar('acepta', 'Debes aceptar para poder enviar tu inscripción.'); ok = false; }
      if (!ok) { enfocarPrimerError(); return; }

      var f = new FormData(form), pago = f.get('pago');
      var cuerpo = new FormData();
      [['categoria', selCat.value], ['pago', pago], ['pais', pais.value.trim()], ['nombre', f.get('nombre')], ['apellido', f.get('apellido')],
       ['documento', f.get('documento')], ['correo', f.get('correo')], ['telefono', f.get('telefono')], ['institucion', f.get('institucion')],
       ['acepta', 'true'], ['sitio_web', f.get('sitio_web')]].forEach(function (p) { cuerpo.append(p[0], p[1] == null ? '' : p[1]); });
      if (metodo === 'ach' || metodo === 'yappy') {
        cuerpo.append('referencia_pago', form.elements.referencia_pago.value.trim());
        cuerpo.append('comprobante', inputArchivo.files[0]);
      } else if (metodo === 'tarjeta') {
        cuerpo.append('account_token', tokenTarjeta); cuerpo.append('cvv', form.elements.cvv.value);
      }
      enviando(true);
      var ctl = window.AbortController ? new AbortController() : null, tmo = ctl ? setTimeout(function () { ctl.abort(); }, 40000) : null;
      enviarForm(datos.endpoint, cuerpo, ctl && ctl.signal).then(function (j) {
        if (!j.ok) {
          (j.campos || []).forEach(function (n) { marcar(n === 'tarjeta' ? 'account_token' : n, j.error); });
          if ((j.campos || []).indexOf('tarjeta') !== -1 && metodo === 'tarjeta') cargarTarjeta();
          if (!(j.campos || []).length) errorGeneral(j.error || 'No se pudo enviar. Intenta de nuevo.');
          else { errorGeneral(j.error); enfocarPrimerError(); }
          return;
        }
        mostrarExito(j);
      }).catch(function (x) {
        errorGeneral(x && x.name === 'AbortError' ? 'La conexión tardó demasiado. Revisa tu internet e inténtalo de nuevo: no se duplicará tu inscripción.' : 'No hay conexión con el servidor. Inténtalo de nuevo en unos segundos.');
      }).then(function () { if (tmo) clearTimeout(tmo); enviando(false); });
    });

    // ---- Pantalla de confirmación
    var ok = $('#registro-ok');
    function mostrarExito(j) {
      $('#ok-titulo').textContent = j.reenvio ? 'Ya tenías una inscripción' : '¡Recibimos tu solicitud!';
      $('#ok-saludo').textContent = (j.nombre ? j.nombre + ', t' : 'T') + (j.reenvio ? 'e reenviamos tus datos y las indicaciones de pago.' : j.pagado ? 'u pago con tarjeta fue aprobado y tu cupo está confirmado.' : 'u cupo se confirma cuando verifiquemos tu pago.');
      $('#ok-paso').textContent = j.pagado ? 'Tu pago fue aprobado' : 'Siguiente paso: tu pago'; $('#ok-paso2').classList.toggle('hidden', !!j.pagado);
      $('#ok-codigo').textContent = j.codigo; $('#ok-categoria').textContent = j.categoria; $('#ok-precio').textContent = j.precio;
      $('#ok-pago').textContent = j.pago; $('#ok-correo').textContent = j.correo;
      $('#ok-instrucciones').textContent = j.instrucciones || ('Envía tu comprobante, con tu código, a ' + (j.contacto || datos.correo) + '.');
      form.classList.add('hidden'); ok.classList.remove('hidden'); ok.focus({ preventScroll: true });
      ok.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    $('#ok-copiar').addEventListener('click', function () {
      var b = this, t = $('#ok-codigo').textContent;
      function listo() { b.textContent = '¡Copiado!'; setTimeout(function () { b.textContent = 'Copiar código'; }, 2000); }
      if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(t).then(listo, function () {});
      else { var r = document.createRange(); r.selectNode($('#ok-codigo')); var s = getSelection(); s.removeAllRanges(); s.addRange(r); try { document.execCommand('copy'); listo(); } catch (x) {} }
    });
    $('#ok-otra').addEventListener('click', function () {
      form.reset(); metodo = ''; form.elements.pago.value = ''; pintarPagos(); pintarPrecio(); pintarExtranjero();
      $$('.campo-error', form).forEach(function (x) { x.classList.remove('campo-error'); x.removeAttribute('aria-invalid'); });
      $$('.campo-msg', form).forEach(function (x) { x.hidden = true; });
      ok.classList.add('hidden'); form.classList.remove('hidden'); campoEl('nombre').focus();
    });
  }

  // ---- Formulario de contacto
  var fc = $('#contacto-form');
  if (fc) {
    var msg = $('#contacto-msg');
    fc.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!fc.checkValidity()) { fc.reportValidity(); return; }
      var f = new FormData(fc), b = $('button', fc);
      b.disabled = true;
      enviarJson(datos.contacto, { nombre: f.get('nombre'), correo: f.get('correo'), mensaje: f.get('mensaje'), sitio_web: f.get('sitio_web') })
        .then(function (j) {
          msg.className = 'rounded-xl px-4 py-3 text-sm font-medium ' + (j.ok ? 'bg-esm-50 text-esm-800' : 'bg-red-50 text-red-700');
          msg.textContent = j.ok ? '¡Gracias! Recibimos tu consulta y te responderemos pronto.' : (j.error || 'No se pudo enviar.');
          if (j.ok) fc.reset();
        }).catch(function () { msg.className = 'rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700'; msg.textContent = 'No hay conexión con el servidor.'; })
        .then(function () { b.disabled = false; });
    });
  }

  // ---- Internacionales: el botón del banner abre el panel con las dos opciones (estadía / inscripción)
  var interAbrir = $('#inter-abrir'), interPanel = $('#inter-opciones');
  if (interAbrir && interPanel && typeof interPanel.showModal === 'function') {
    interAbrir.addEventListener('click', function () { interPanel.showModal(); });
    interPanel.addEventListener('click', function (e) {
      // clic fuera del cuadro (en el fondo) o en un elemento con data-cerrar
      if (e.target === interPanel || e.target.closest('[data-cerrar]')) interPanel.close();
    });
  }
})();

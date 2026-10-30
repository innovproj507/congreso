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

    // Medio de pago: se marca el elegido y se muestran sus instrucciones antes de enviar
    var radios = $$('input[name=pago]', form), cajaPago = $('#pago-instrucciones');
    function pintarPagos() {
      var elegido = '';
      radios.forEach(function (r) { r.parentNode.classList.toggle('is-on', r.checked); if (r.checked) elegido = r.value; });
      var t = (datos.instrucciones || {})[elegido] || '';
      cajaPago.textContent = t; cajaPago.classList.toggle('hidden', !t);
    }
    radios.forEach(function (r) { r.addEventListener('change', pintarPagos); }); pintarPagos();

    // ---- Validación por campo (mensajes junto al campo, en español)
    var reglas = {
      nombre: function (v) { return v.length < 2 ? 'Escribe tu nombre.' : ''; },
      apellido: function (v) { return v.length < 2 ? 'Escribe tu apellido.' : ''; },
      documento: function (v) {
        if (!v) return 'Escribe tu cédula o pasaporte.';
        if (v.length < 4) return 'Revisa tu cédula o pasaporte: es muy corta.';
        return /^[A-Za-z0-9\-\. ]+$/.test(v) ? '' : 'Solo letras, números y guiones.';
      },
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
    function enviando(si) { boton.disabled = si; spin.classList.toggle('hidden', !si); txtBoton.textContent = si ? 'Enviando…' : 'Enviar inscripción'; }

    form.addEventListener('submit', function (e) {
      e.preventDefault(); err.classList.add('hidden');
      var ok = Object.keys(reglas).map(validar).every(Boolean);
      if (!acepta.checked) { marcar('acepta', 'Debes aceptar para poder enviar tu inscripción.'); ok = false; }
      if (!ok) { enfocarPrimerError(); return; }

      var f = new FormData(form), pago = f.get('pago');
      var cuerpo = {
        categoria: selCat.value, pago: pago, pais: pais.value.trim(),
        nombre: f.get('nombre'), apellido: f.get('apellido'), documento: f.get('documento'), correo: f.get('correo'),
        telefono: f.get('telefono'), institucion: f.get('institucion'), acepta: true, sitio_web: f.get('sitio_web')
      };
      enviando(true);
      var ctl = window.AbortController ? new AbortController() : null, tmo = ctl ? setTimeout(function () { ctl.abort(); }, 40000) : null;
      enviarJson(datos.endpoint, cuerpo, ctl && ctl.signal).then(function (j) {
        if (!j.ok) {
          (j.campos || []).forEach(function (n) { if (reglas[n]) marcar(n, j.error); });
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
      $('#ok-saludo').textContent = (j.nombre ? j.nombre + ', t' : 'T') + (j.reenvio ? 'e reenviamos tus datos y las indicaciones de pago.' : 'u cupo se confirma cuando verifiquemos tu pago.');
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
      form.reset(); pintarPagos(); pintarPrecio(); pintarExtranjero();
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
})();

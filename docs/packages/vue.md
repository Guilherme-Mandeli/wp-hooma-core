# Vue.js Package

Vue.js es una biblioteca progresiva de JavaScript para construir interfaces de usuario interactivas y reactivas en la web. En Hooma Core, Vue.js está disponible de forma nativa como un Package reutilizable de infraestructura para evitar instalaciones complejas o duplicación de dependencias.

## 1. ¿Qué es este Package?
Este paquete expone los archivos compilados de producción y desarrollo de Vue.js (versión `3.5.22`).

- **Identificador**: `vue`
- **Tipo**: `javascript`
- **Puntos de entrada**:
  - `production`: `dist/vue.global.prod.js`
  - `development`: `dist/vue.global.js`

---

## 2. ¿Cuándo debería usarlo?
Deberías utilizar este Package cuando:
- Estés construyendo interfaces interactivas complejas (paneles de control, formularios dinámicos, tablas reactivas, etc.) en los paneles de administración de tus Módulos.
- Necesites reactividad en tiempo real sin tener que escribir JavaScript vanilla de forma engorrosa.
- Quieras unificar la versión de Vue.js utilizada en todos tus desarrollos para optimizar la carga del sitio.

---

## 3. Compatibilidad con Servicios de Hooma
Este paquete interactúa de forma directa con los siguientes servicios del Core:

- **Assets Service** (`Hooma::assets()`): Utilizado para registrar y encolar los puntos de entrada (URLs públicas) de Vue en la cola oficial de scripts de WordPress.

---

## 4. ¿Cómo se carga con Hooma?

Para cargar y utilizar Vue en el controlador o archivo principal de tu Módulo, suscríbete al gancho `admin_enqueue_scripts` de WordPress y utiliza el servicio de Packages y Assets:

```php
add_action('admin_enqueue_scripts', function() {
    if (!Hooma::packages()->exists('vue')) {
        return;
    }

    $vue = Hooma::packages()->get('vue');
    
    // Determinar la entrada adecuada (desarrollo o producción) según el entorno WP_DEBUG
    $entry_key = (defined('WP_DEBUG') && WP_DEBUG) ? 'development' : 'production';
    $vue_url   = $vue->get_entry_url($entry_key);

    // Registrar y encolar el script
    Hooma::assets()->enqueue_script(
        'hooma-package-vue',
        $vue_url,
        array(),
        $vue->get_version(),
        true
    );
});
```

---

## 5. Ejemplos de Uso

### Ejemplo A: Aplicación Básica (Reactividad)
Crea una estructura HTML reactiva simple.

**HTML / View (views/index.php):**
```html
<div id="hooma-vue-app" style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
    <h2>{{ message }}</h2>
    <button @click="increment" class="button button-primary">Contador: {{ count }}</button>
</div>
```

**JavaScript (assets/js/app.js):**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const { createApp, ref } = Vue;

    createApp({
        setup() {
            const message = ref('¡Hola desde Vue con Hooma!');
            const count = ref(0);

            const increment = () => {
                count.value++;
            };

            return {
                message,
                count,
                increment
            };
        }
    }).mount('#hooma-vue-app');
});
```

---

### Ejemplo B: Componentes Locales
Estructura y encapsulación de componentes.

```javascript
const MyButton = {
    props: ['label'],
    template: `<button class="button">{{ label }}</button>`
};

Vue.createApp({
    components: {
        'my-button': MyButton
    }
}).mount('#app');
```

---

### Ejemplo C: Solicitud AJAX (HTTP Integration)
Uso de Vue.js para interactuar con APIs remotas.

```javascript
Vue.createApp({
    setup() {
        const items = Vue.ref([]);
        
        Vue.onMounted(() => {
            fetch('/wp-json/hooma/v1/data')
                .then(response => response.json())
                .then(data => {
                    items.value = data;
                });
        });

        return { items };
    }
}).mount('#app');
```

---

## 6. Buenas Prácticas
1. **Separación de Lógica**: Mantén tu JavaScript reactivo en archivos `.js` separados bajo la carpeta `assets/` de tu módulo, en lugar de inyectar bloques de `<script>` en las vistas PHP.
2. **Carga en el Pie de Página**: Asegúrate de encolar tanto a Vue como a tu script de aplicación en el footer (`$in_footer = true`) para evitar bloqueos del renderizado inicial.
3. **Control de Versiones**: Al encolar los archivos, utiliza `$vue->get_version()` como argumento de versión para limpiar la caché del navegador cuando actualices el Package.

---

## 7. Errores Comunes
- **"Vue is not defined"**: Asegúrate de que tu script dependa explícitamente de `'hooma-package-vue'` al registrarlo, para que WordPress fuerce la carga de Vue.js *antes* de tu archivo de aplicación:
  ```php
  Hooma::assets()->enqueue_script('mi-modulo-js', 'assets/js/app.js', array('hooma-package-vue'));
  ```
- **"Mount target is not found"**: Ocurre si inicializas la aplicación Vue antes de que el DOM cargue. Envuelve la inicialización en un listener `DOMContentLoaded` o utilízalo al final de la página.

---

## 8. Recursos Adicionales
- [Sitio oficial de Vue.js](https://vuejs.org)
- [Guía oficial de Composición API](https://vuejs.org/guide/introduction.html)

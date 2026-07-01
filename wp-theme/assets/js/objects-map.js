document.addEventListener('DOMContentLoaded', () => {
  const mapElement = document.getElementById('objects-yandex-map');
  if (!mapElement) return;

  const rawProjects = mapElement.getAttribute('data-projects');
  let projects = [];
  try {
    projects = JSON.parse(rawProjects || '[]');
  } catch (e) {
    console.error('Error parsing projects data for map:', e);
    return;
  }

  // If there are no projects, hide the section
  if (projects.length === 0) {
    const parentSection = mapElement.closest('.objects-map-section');
    if (parentSection) parentSection.style.display = 'none';
    return;
  }

  // Lazy loading observer
  let mapLoaded = false;
  const loadMap = () => {
    if (mapLoaded) return;
    mapLoaded = true;

    // Check if ymaps is loaded
    if (typeof ymaps === 'undefined') {
      console.error('Yandex Maps API is not loaded');
      return;
    }

    ymaps.ready(initMap);
  };

  const initMap = () => {
    const mapContainer = document.getElementById('objects-yandex-map-live');
    const placeholder = document.getElementById('objects-yandex-map-placeholder');
    if (!mapContainer) return;

    // Remove placeholder
    if (placeholder) {
      placeholder.style.opacity = '0';
      setTimeout(() => placeholder.remove(), 400);
    }

    // Moscow as fallback center
    const defaultCenter = [55.7558, 37.6173];
    const map = new ymaps.Map(mapContainer, {
      center: defaultCenter,
      zoom: 9,
      controls: ['zoomControl', 'fullscreenControl']
    }, {
      searchControlProvider: 'yandex#search'
    });

    // Enable scroll zoom by default as requested
    map.behaviors.enable('scrollZoom');

    // Dynamic marker scaling based on zoom level (adds objects-map--zoom-X class to container)
    const updateMarkerScaling = () => {
      const zoom = map.getZoom();
      for (let i = 1; i <= 23; i++) {
        mapElement.classList.remove(`objects-map--zoom-${i}`);
      }
      mapElement.classList.add(`objects-map--zoom-${Math.round(zoom)}`);
    };

    map.events.add('boundschange', updateMarkerScaling);
    updateMarkerScaling();

    // Custom Circular Layout Template for projects
    const MyMarkerLayout = ymaps.templateLayoutFactory.createClass(
      '<div class="map-project-marker-container">' +
      '  <div class="map-project-marker" style="background-image: url(\'$[properties.image]\');"></div>' +
      '  <div class="map-project-marker-pin"></div>' +
      '</div>'
    );

    const myGeoObjects = [];

    projects.forEach(project => {
      if (!project.coords || project.coords.length !== 2) return;

      const titleHtml = `<strong style="font-size:14px; font-weight:700; color:#111827; display:block; margin-bottom:4px;">${project.title}</strong>`;
      const addressHtml = project.address ? `<span style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">${project.address}</span>` : '';
      const descHtml = project.description ? `<p style="margin:6px 0; font-size:13px; line-height:1.4; color:#374151;">${project.description}</p>` : '';
      
      const placemark = new ymaps.Placemark(project.coords, {
        hintContent: project.title + (project.address ? ' (' + project.address + ')' : ''),
        image: project.image || '', // Pass project image URL to layout properties
        balloonContent: `<div style="font-family: 'Inter', -apple-system, sans-serif; color: #111827; padding: 4px 6px; max-width: 240px;">
          ${titleHtml}
          ${addressHtml}
          ${descHtml}
          <a href="${project.link}" style="display: inline-block; margin-top: 6px; font-size: 13px; font-weight: 600; color: #167b88; text-decoration: none; border-bottom: 1px dashed #167b88;">Перейти к проекту →</a>
        </div>`
      }, {
        iconLayout: MyMarkerLayout,
        iconSize: [24, 26],
        iconOffset: [-12, -26],
        iconShape: {
          type: 'Rectangle',
          coordinates: [
            [0, 0], [24, 26]
          ]
        }
      });

      map.geoObjects.add(placemark);
      myGeoObjects.push(placemark);
    });

    // Auto fit map bounds to show all markers
    if (myGeoObjects.length > 0) {
      if (myGeoObjects.length === 1) {
        map.setCenter(projects[0].coords, 12);
      } else {
        const bounds = map.geoObjects.getBounds();
        if (bounds) {
          map.setBounds(bounds, {
            checkZoomRange: true,
            zoomMargin: 46
          });
        }
      }
    }
  };

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          loadMap();
          observer.disconnect();
        }
      });
    }, {
      rootMargin: '300px 0px',
      threshold: 0.1
    });
    observer.observe(mapElement);
  } else {
    loadMap();
  }
});

import 'ol/ol.css'; // Styles für OpenLayers
import Map from 'ol/Map';
import View from 'ol/View';
import TileLayer from 'ol/layer/Tile';
import OSM from 'ol/source/OSM';
import { fromLonLat, toLonLat } from 'ol/proj';
import { Point } from 'ol/geom';
import { Feature } from 'ol';
import { Icon, Style } from 'ol/style';
import { Vector as VectorLayer } from 'ol/layer';
import { Vector as VectorSource } from 'ol/source';
import Tooltip from 'bootstrap/js/dist/tooltip';

export class OpenMap
{
    config = {
        domId: '',
        cityCoords: [0, 0],
        iconUrl: '',
        zoom: 12,
        streetInputId: '',
        lonInputId: 0,
        latInputId: 0,
        setEditMode: false,
    };
    map;
    vectorSource;
    vectorLayer;
    lastFeatureIcon;
    lastMapIcon;
    mapIcons;

    constructor(config) {
        this.config = Object.assign({},this.config,  config);
        this.config.cityCoords = fromLonLat(config.cityCoords);
        this.vectorSource = new VectorSource();
        this.vectorLayer = new VectorLayer({
            source: this.vectorSource
        });
        this.lastFeatureIcon = null;
        this.lastMapIcon = null;
        this.mapIcons = [];
        this.device = /Mobi|Android/i.test(navigator.userAgent) ? 'mobile' : 'desktop';
        this.setMap();
    }
    setMap ()
    {
        this.map = new Map({
            target: this.config.domId, // ID des HTML-Elements, in dem die Karte dargestellt wird
            layers: [
                new TileLayer({
                    source: new OSM(), // OpenStreetMap als Hintergrundkarte
                }),
            ],
            view: new View({
                center: this.config.cityCoords,
                zoom: this.config.zoom,
            }),
        });
        this.map.addLayer(this.vectorLayer);

        if (this.config.setEditMode) {
            this.map.on('click', event => {
                const coordinates = event.coordinate;
                const lonLat = toLonLat(coordinates);
                const lon = lonLat[0];
                const lat = lonLat[1];

                this.setStreetName(lon, lat);
                this.setIconFeature (coordinates);
            });
        }
    }

    setStreetIcon (lon, lat)
    {
        try {
            const coordinates = fromLonLat([lon, lat]);
            const lonLat = toLonLat(coordinates);
            this.setStreetName(lonLat[0], lonLat[1]);
            this.setIconFeature(coordinates);
            this.map.getView().setCenter(coordinates);
            this.map.getView().setZoom(14);
        } catch (e) {
            if (!(e instanceof Error)) {
                e = new Error(e);
            }
            console.error(e.message);
        }
    }

    setTooltip ()
    {
        this.tooltipContainer = document.createElement('div');
        this.tooltipContainer.setAttribute('data-bs-toggle', 'tooltip'); // Bootstrap Tooltip
        this.tooltipContainer.setAttribute('title', ''); // Dynamischer Inhalt
        this.tooltipContainer.style.position = 'absolute';
        this.tooltipContainer.style.pointerEvents = 'none';
        this.tooltipContainer.style.display = 'none';
        this.tooltipContainer.id = 'tooltip';
        this.tooltipContainer.classList.add('ol-tooltip');
        document.body.appendChild(this.tooltipContainer);

        this.bootstrapTooltip = new Tooltip(this.tooltipContainer);

        this.map.on('pointermove', (event) => this.handleTooltip(event));
        this.map.on('pointerdown', (event) => this.handleTooltip(event));
    }

    handleTooltip(event) {
        const feature = this.map.forEachFeatureAtPixel(event.pixel, (feat) => feat);

        if (feature && feature.get('info')) {

            const info = feature.get('info');

            if (this.device === 'desktop') {
                this.tooltipContainer.innerHTML = `<strong>Straße:</strong> ${info.streetName}<br><strong>Erste Zählung am:</strong> ${info.createdAt}<br>`;

                this.bootstrapTooltip.update();

                this.tooltipContainer.style.left = `${Math.round(event.originalEvent.clientX  + window.scrollX)}px`;
                this.tooltipContainer.style.top = `${Math.round(event.originalEvent.clientY + window.scrollY)}px`;

                this.tooltipContainer.style.display = 'block';
            }

            if (event.originalEvent.type === 'pointerdown') {
                if (this.config.iconMenuEvent) {
                    this.config.iconMenuEvent.click(info);
                }
                event.preventDefault();
                this.tooltipContainer.style.display = 'none';
            }
        } else {
            this.tooltipContainer.style.display = 'none';
        }
    }

    setMapIcon (config) {

        const coordinates = fromLonLat([config.lon, config.lat]);
        const iconFeature = new Feature({
            geometry: new Point(coordinates)
        });
        const iconConfig = {
            anchor: [0.5, 1],
            anchorXUnits: 'fraction',
            anchorYUnits: 'pixels',
            scale: 1.0,
            color: '#ff6600',
            crossOrigin: 'anonymous',
            src: config.iconUrl,
        };

        iconFeature.set('info', {
            streetName: config.streetName || 'Keine Straße angegeben',
            createdAt: config.createdAt || 'Kein Datum gefunden',
            iconId: config.iconId || 'Keine Id gefunden',
        });

        iconFeature.setStyle(new Style({image: new Icon(iconConfig)}));

        this.vectorSource.addFeature(iconFeature);
        this.map.render();
        this.mapIcons[config.iconId] = {
            iconFeature: iconFeature,
            iconConfig: iconConfig,
            coordinates: coordinates
        };
    }

    setMapIconHighlight (config) {
        const coordinates = fromLonLat([config.lon, config.lat]);
        this.map.getView().setCenter(coordinates);
        this.map.getView().setZoom(config.zoom);
        if (this.lastMapIcon) {
            this.changeIconColor(this.lastMapIcon, '#ff6600');
        }
        this.changeIconColor(this.mapIcons[config.iconId], '#81e616', 2.0);
        this.lastMapIcon = this.mapIcons[config.iconId];

        this.map.render();
    }

    changeIconColor(icon, color, scale = 1.0) {
        icon.iconConfig.color = color;
        icon.iconConfig.scale = scale;
        const newStyle = new Style({
            image: new Icon(icon.iconConfig),
        });
        icon.iconFeature.setStyle(newStyle);
    }

    setIconFeature (coordinates)
    {
        const iconFeature = new Feature({
            geometry: new Point(coordinates)
        });

        iconFeature.setStyle(new Style({
            image: new Icon({
                anchor: [0.5, 1],
                anchorXUnits: 'fraction',
                anchorYUnits: 'pixels',
                scale: 1.0,
                color: '#ff6600',
                crossOrigin: 'anonymous',
                src: this.config.iconUrl,
            })
        }));

        if (this.lastFeatureIcon) {
            this.vectorSource.removeFeature(this.lastFeatureIcon);
        }
        this.vectorSource.addFeature(iconFeature);
        this.map.render();
        this.lastFeatureIcon = iconFeature;
    }

    setIconColor (base64IconUrl, color)
    {
        const decodedSvg = atob(base64IconUrl.split(',')[1]);

        // Erstelle ein SVG-Element und setze den dekodierten Inhalt
        const svgElement = document.createElement('div');
        svgElement.innerHTML = decodedSvg;

        // Jetzt kannst du das SVG-Element manipulieren (z. B. Farbe ändern)
        const svg = svgElement.querySelector('svg');
        svg.setAttribute('fill', color); // Beispiel: Farbe ändern

        // Hol das geänderte SVG zurück als Data-URL
        const svgString = new XMLSerializer().serializeToString(svg);
        const updatedBase64 = btoa(svgString);

        return 'data:image/svg+xml;base64,' + updatedBase64;
    }

    setStreetName (lon, lat)
    {
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`)
            .then(response => response.json())
            .then(data => {
                // Straßennamen in das Input-Feld übertragen
                if (data.address && data.address?.road) {

                    document.getElementById(this.config.streetInputId).value = data.address.road;
                    document.getElementById(this.config.streetDetails).value = JSON.stringify(data.address);
                    document.getElementById(this.config.lonInputId).value = lon.toFixed(6);
                    document.getElementById(this.config.latInputId).value = lat.toFixed(6);
                } else {
                    document.getElementById(this.config.streetInputId).value = "Straße nicht gefunden";
                }
            })
            .catch(error => {
                console.error('Fehler beim Abrufen des Straßennamens:', error);
            });
    }

    escapeHtml (unsafe)
    {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }
    hexToRgbaArray(hex, alpha = 1) {
        // Entfernt das "#", falls vorhanden
        hex = hex.replace('#', '');

        // Konvertiert 3-stellige Hex-Werte (z. B. #f60) in 6-stellige (z. B. #ff6600)
        if (hex.length === 3) {
            hex = hex.split('').map(h => h + h).join('');
        }

        // Extrahiert die RGB-Werte aus der Hex-Farbe
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);

        // Gibt ein Array [R, G, B, A] zurück
        return [r, g, b, alpha];
    }
}
window.OpenMap = OpenMap;

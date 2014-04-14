var json={
    "map": {
        "title": "EII Santa Coloma",
        "size": "2048",
        "servers": [{
            "name": "Ortofotos ICC",
            "url": "http://mapcache.icc.cat/map/bases_noutm/service?FORMAT=image%2Fjpeg&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SRS=EPSG%3A4326&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=topo&BBOX=2.1746243945313,41.435603918945,2.2340156054688,41.467139075195&WIDTH=1356&HEIGHT=720",
            "layers": [{
                "name": "topo",
                "title": "Ortofoto",
                "legend": ""
            }]
        },
        {
            "name": "Geoserver Progess",
            "url": "http://si.progess.com:8008/geoserver/wms?LAYERS=barris,recursos&FORMAT=image%2Fgif&TRANSPARENT=true&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&SRS=EPSG%3A4326&BBOX=2.1746243945313,41.435603918945,2.2340156054688,41.467139075195&WIDTH=1356&HEIGHT=720",
            "layers": [{
                "title": "Barris",
                "legend": "http://si.progess.com:8008/geoserver/wms?LAYER=barris&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
            },
            {
                "title": "Recursos",
                "legend": "http://si.progess.com:8008/geoserver/wms?LAYER=recursos&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
            }]
        }]
    }
};
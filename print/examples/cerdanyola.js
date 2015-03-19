var json2={
    "map": {
        "title": "Cerdanyola: urbanisme",
        "size": 1200,
        "scale": 5000,
        "epsg": 23031,        
        "geographic": false,
        "servers": [
            {
                "name": "Topo ICC",
				"type": "wms",
            "url": "http://shagrat.icc.es/lizardtech/iserv/ows?FORMAT=image%2Fjpeg&VERSION=1.1.1&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SERVICE=WMS&REQUEST=GetMap&STYLES=&LAYERS=mtc10m&SRS=EPSG%3A25831&BBOX=424476.90754292,4592004.2104021,429795.02967114,4594613.0006599&WIDTH=1507&HEIGHT=739",
            "layers": [{
                "name": "topo",
                "title": "Topogràfic"
                }]
            },
        {
            "name": "Mapserver Cerdanyola planejament",
			"type": "wms",
            "url": "http://sig.cerdanyola.cat/wms56/cerdanyola/servidor/planejament?FORMAT=image%2Fpng&TRANSPARENT=true&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&STYLES=&LAYERS=qualificacions,cataleg,expedients&SRS=EPSG%3A25831&BBOX=424476.90754292,4592004.2104021,429795.02967114,4594613.0006599&WIDTH=1507&HEIGHT=739",
            "opacity": 0.8,
            "layers": [{
                "title": "Qualificacions",
                "legend": "http://sig.cerdanyola.cat/wms56/cerdanyola/servidor/planejament?REQUEST=GetLegendGraphic&VERSION=1.1.1&LAYER=qualificacions&FORMAT=image/png&SERVICE=WMS"
            },{
                "title": "Catàleg",
                "legend": "http://sig.cerdanyola.cat/wms56/cerdanyola/servidor/planejament?REQUEST=GetLegendGraphic&VERSION=1.1.1&LAYER=cataleg&FORMAT=image/png&SERVICE=WMS"
            },{
                "title": "Expedients",
                "legend": "http://sig.cerdanyola.cat/wms56/cerdanyola/servidor/planejament?REQUEST=GetLegendGraphic&VERSION=1.1.1&LAYER=expedients&FORMAT=image/png&SERVICE=WMS"
            }]            
        }],
        "config": {
            "showNorth": true,
            "showEpsg": true,
            "showScale": true,
            "showLegend": true,
            "showLogo": false,
            "boxGap": 3
        }
    }
};
WMS2PDF
=======

WMS2PDF is a library that prints a WMS (Web Map Service) services collection to a PDF file. 
Licensed under  http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License

Uses Anthony Martin's GeoLocation library (https://github.com/anthonymartin/GeoLocation.php) 
to calculate distance between two points. Many thanks to him!
Geolocation is licensed under CC 3.0 license: http://creativecommons.org/licenses/by/3.0/

JSON params
-----------

WMS2PDF és una llibreria per imprimir serveis WMS a un fitxer PDF. Accepta dos paràmetres per POST. El paràmetre optatiu 'layout' que permet utilitzar una classe específica per a definir el mapa; i també
el paràmetre 'printData' que conté dades en format JSON que permeten definir multitud de paràmetres: títol, escala, projecció, llegendes ... Per començar el més senzill és consultar l'exemple a mcrit.htm i diversos exemples a la carpeta /examples però a continuació es descriuen els elements de l'estructura de l'arxiu JSON.

- "title": títol del mapa
- "size": mida (en píxels) de la petició WMS. Com més gran, més qualitat d'imatge, però la petició consumeix més temps. I en cap cas cal excedir la mida màxima que accepta el servidor (per exemple, en el cas del de l'ICC, uns 4000px)
- "epsg": codi EPSG de la projecció
- "geographic": si són coordenades geogràfiques o projectades
- "scale": escala a la que s'ha d'obrir el mapa (ignorant els paràmetres de servers->url)
- "servers": array de les peticions WMS. L'aproximació és bottom-to-top: les primeres capes queden a sota de les posteriors. Per a fer-ho al màxim de senzill per al programador front-end, permet rebre la URL "tal qual" (inclòs el BBOX, el format, etc) i l'aplicatiu ja s'encarrega de parsejar els valors de la petició WMS. Si es vol llegenda, dins de cada "server" cal posar un array de "layers" amb el nom i la URL (estàtica o dinàmica) de la llegenda. 
- "config": possibilitat de sobreescriure molts paràmetres de configuració, com si cal mostrar la llegenda, la fletxa de nord, la projecció ... se'n poden afegir més

Exemple JSON:
```
{
  "map": {
    "title": "Polinyà: urbanisme",
    "size": 1500,
    "scale": 5000,
    "epsg": 23031,
    "geographic": false,
    "servers": [
      {
        "name": "Mapserver Polinyà planejament",
        "url": "http://oslo.geodata.es/wms52/polinya/servidor/planejament_pol?FORMAT=image%2Fpng&TRANSPARENT=true&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&STYLES=&EXCEPTIONS=application%2Fvnd.ogc.se_inimage&LAYERS=qualificacions,sectors&SRS=EPSG%3A23031&BBOX=423984.79450989,4598473.8764316,434515.20549011,4603776.1235684&WIDTH=1492&HEIGHT=751",
        "layers": [
          {
            "title": "Qualificacions",
            "legend": "http://oslo.geodata.es/wms52/polinya/servidor/planejament_pol?REQUEST=GetLegendGraphic&VERSION=1.1.1&LAYER=qualificacions&FORMAT=image/png&SERVICE=WMS"
          },
          {
            "title": "Règim del sòl",
            "legend": "http://oslo.geodata.es/wms52/polinya/servidor/planejament_pol?REQUEST=GetLegendGraphic&VERSION=1.1.1&LAYER=sectors&FORMAT=image/png&SERVICE=WMS"
          }
        ]
      }
    ],
    "config": {
      "showNorth": true,
      "showEpsg": true,
      "showScale": true,
      "showLegend": true,
      "showLogo": false,
      "boxGap": 3
    }
  }
}
```

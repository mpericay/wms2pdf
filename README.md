WMS2PDF és una llibreria per imprimir serveis WMS a un fitxer PDF. Accepta el paràmetre 'printData' per POST que conté dades en format JSON que permeten definir multitud de paràmetres: títol, escala, projecció, llegendes ... Per començar el més senzill és consultar l'exemple a index.htm i diversos exemples a la carpeta /examples però a continuació es descriuen els elements de l'estructura de l'arxiu JSON.

- "title": títol del mapa
- "size": mida (en píxels) de la petició WMS. Com més gran, més qualitat d'imatge, però la petició consumeix més temps. I en cap cas cal excedir la mida màxima que accepta el servidor (per exemple, en el cas del de l'ICC, uns 4000px)
- "epsg": codi EPSG de la projecció
- "geographic": si són coordenades geogràfiques o projectades
- "scale": escala a la que s'ha d'obrir el mapa (ignorant els paràmetres de servers->url)
- "servers": array de les peticions WMS. L'aproximació és bottom-to-top: les primeres capes queden a sota de les posteriors. Per a fer-ho al màxim de senzill per al programador front-end, permet rebre la URL "tal qual" (inclòs el BBOX, el format, etc) i l'aplicatiu ja s'encarrega de parsejar els valors de la petició WMS. Si es vol llegenda, dins de cada "server" cal posar un array de "layers" amb el nom i la URL (estàtica o dinàmica) de la llegenda. 
- "config": possibilitat de sobreescriure molts paràmetres de configuració, com si cal mostrar la llegenda, la fletxa de nord, la projecció ... se'n poden afegir més
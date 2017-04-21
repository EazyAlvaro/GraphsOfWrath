# GraphsOfWrath

Hobby project involving graphs, generated from a Listography.com page where my 
girlfriend tracks her book reading and movie viewing.

It used to generate .png files with "amenadiel/jpgraph" 3.6.8, but i am now 
reworking it to generate ChartJS images on a HTML5 canvas, because it's more 
awesome. Much of this retooling is still ongoing.

I originally wrote it CakePHP as as skill-level demo and POC in 2013, and 
started rewriting it recently to keep my Symfony skills alive, as i started 
learning Symfony in 2.3 while 3 is out now and 4 is on the horizon.

Accordingly i took the old-fashioned approach and made a Bundle for my separate 
set of functionalities, apparently you're supposed to use just the AppBundle in 
SF3 but i'll skip that habit as SF4 won' t even have an AppBundle.

Original Comments were in Dutch, i will be rewriting this as i refactor the 
attached code.

TODO (yes i could use GitHub tickets, but i dont actually expects pull requests)
---
* Unit tests (oldfashioned SF way, or maybe CodeCeption)ss
* Behat Tests (through selenium or maybe CodeCeption)      
* AJAX graph selection in a one-by one interface
* All data in dynamic overlays with dynamic line-coloring ( nice-to-have)
* Download button for the graphs
* fallback data views/templates for when stats data has not been generated




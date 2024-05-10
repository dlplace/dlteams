document.title = document.title.replace('GLPI', 'DLTeams');

let favicon = document.createElement('link');
favicon.rel = "icon";
favicon.href = location.origin + CFG_GLPI.root_doc + "/" + GLPI_PLUGINS_PATH.dlteams +"/images/favicon.jpg";
document.getElementsByTagName('head')[0].appendChild(favicon);

let question_mark = document.querySelectorAll("a.fa.fa-question")[0];
question_mark.href = location.origin + CFG_GLPI.root_doc + "/faq2";

let copyright = document.createElement('a');
copyright.href = "https://dlteams.fr/";
copyright.innerHTML = "dlplace.eu © " + new Date().getFullYear() + "&nbsp;&nbsp;&mdash;&nbsp;&nbsp;";
copyright.className = "copyright";
// Insert copyright (<a> tag) into DOM
document.querySelectorAll('#footer>table>tbody>tr>td')[0].prepend(copyright);

var div = document.getElementById("footer");

console.log(div.innerHTML);

//remove this line to see the original footer code
div.innerHTML="<style>.rightside{text-align:right !important}</style><table role=\"presentation\"><tbody><tr><td class=\"rightside\"><a href=\"https://dlteams.fr/\" class=\"copyright\">dlplace.eu © 2022&nbsp;&nbsp;—&nbsp;&nbsp;</a><a href=\"http://glpi-project.org/\" title=\"Powered by Teclib and contributors\" class=\"copyright\">GLPI 9.5.6 Copyright (C) 2015-2021 Teclib' and contributors</a></td></tr></tbody></table>";



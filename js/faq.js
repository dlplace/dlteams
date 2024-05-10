document.title = document.title.replace('GLPI', 'DL Place');

// Set nice links for elements
setInterval( function () {
    let links = document.querySelectorAll('a.faq');
    if (links.length === 0) {
        // "Parcourir" tab
        links = document.querySelectorAll('.kb > a');
    }
    if (links[0]) {
        for (const link of links) {
            link.href = link.href.replace('front/knowbaseitem.form.php', 'faq');
            link.href = link.href.replace('front/helpdesk.faq.php', 'faq');
        }
    }
}, 100);

// Improve security for outside origin links
let glpi_links = document.querySelectorAll('[href*="glpi-project.org"]');
for (const glpi_link of glpi_links) {
    glpi_link.target = "noreferrer";
}

// SEO : add meta description tag
let meta = document.createElement('meta');
meta.setAttribute('name', 'description');
meta.content = "Obtenez toutes les réponses sur vos questions à propos du RGPD et de la gestions de la conformité";
document.getElementsByTagName('head')[0].appendChild(meta);
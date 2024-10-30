<?php
/*
Plugin Name: Bitatags
Plugin URI: http://diavolo.us/projects/wordpress/
Description: Permite generar r&aacute;pidamente etiquetas para que tus post puedan ser indexados por Bitacoras.com sin 
necesidad de pegar el c&oacute;digo HTML necesario para cada una de tus anotaciones.
&iquest;C&oacute;mo integrar a tu blog? S&oacute;lo inserta este pedazo de c&oacute;digo en tu plantilla donde quieres 
que sea visualizado los tags  <code>&lt;?php the_bita_tags(); ?&gt;</code> 
M&aacute;s instrucciones: <a href="http://diavolo.us/tag/bitatags/">Bitatags</a> .
Version: 0.7
Author: Diavolo
Author URI: http://diavolo.us/


  Copyright 2005, 2006  Stephanie Booth  (steph@climbtothestars.org) 

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

INSTRUCCIONES
============

Al crear un post, puedes agregar tags usando la caja de texto que se mostrara a continuacion. Si quieres usar 2 o mas palabras como tag, usa "+" para unirlas, por ejemplo : latino america latino+america

Agrega el siguiente codigo en tu plantilla donde quieras que aparezcan los tags 

<?php the_bita_tags(); ?> 		 	


Te deberia quedar algo como :

<?php the_content('Continuar leyendo &raquo;'); ?>
<?php the_bita_tags(); ?>

Los tags aparecen como links dentro de <p class="tags">Tags: ...</p>.

Tambien puedes agregarle HTML, como por ejemplo (gracias a Scott):

<?php the_bita_tags("<ul><li>", "</li></ul>", "</li><li>"); ?>

Los tags se guardan en los campos personalizados de Wordpress como listas separadas por comas, si necesitas cambiar algo en eso puedes enredarte en ello cambiando la funcion :					

 function get_bunny_tags_list().

ADVERTENCIA : si su pagina no usa utf-8 u otros caracteres (como acentos) probablemente sus tags no se indexen correctamente.

SETTINGS
========

If you want the plugin to try and use a comma-separated "keywords" post_meta field if there is no space-separated "tags" field available, replace "true" below by "false": */

$bunny_strict=true;

/* If you want to use something else than the Technorati tagspace, just edit the plugin to replace all links to technorati.com by whatever you want.

If you want to change the separator to something else than a space, just edit the call to the function get_bunny_tags() in get_bunny_tags_list()


CHANGELOG
=========

0.1  - Initial release 18.01.2005
0.2  - Added "tags" textarea to post.php
	 - Modified the_bunny_keyword_tags(): uses 'tags' if available
0.21 - Added missing "Save Post" action hook (tags weren't saved on drafts)	 
0.3  - Include tags in post content in feeds for better parsing by Technorati
	 - Replaced the_bunny_keyword_tags() by setting $bunny_strict=false
0.31 - Now encodes accented characters in tag URIs (accented tags were breaking Kevin's parser!)
0.32 - Minor tweaks to url (correct encoding function, removed trailing slash) which should *finally* mean the tags will get indexed correctly. See warning above, however.
0.4  - Courtesy of Scott Jarkoff, you can now wrap the list of tags in any HTML you want.
0.41 - Fixed bug messing with the feeds.
0.42 - Code tweaks, no change in functionality.
0.43 - Code tweaks 28.01.2005
0.5  - Now functions with Wordpress 2.0 (03.01.2006)
	 - Added fix for spaces in tags (use "+")
	 - Added support for Pages
0.6  - Fixed the custom field bug caused by WordPress 2.1
     - Now works with WordPress 2.1 also. More details at http://sudarmuthu.com/blog/2007/01/31/wordpress-21-and-custom-field-plugin-gotcha.html
0.6b - Adapted for Bitacoras.com tags
0.7  - Code tweaks

CREDITS
=======

Many thanks in particular to Carthik ( http://blog.carthik.net/ ), who gave me the necessary nudge to start coding this plugin, and to Morgan ( http://doocy.net/ ), who helped me write my first action hooks, and who pointed me to his plugin MiniPosts; you'll recognize some of the code if you compare both plugins ;-) 

Thanks also to Kevin Marks ( http://epeus.blogspot.com/ ) for encouraging me to play about with all the Technorati stuff and helping me iron out some tag url bugs.

Scott Jarkoff ( http://www.jarkolicious.com/ ) made the modification which allows passing HTML formatting of the list in the_bunny_tags($before, $after, $separator). Thanks!

<? this line just makes things pretty in my BBEdit
*/


// retrieve tag values from post_meta
function get_bunny_tags($separator=" ", $meta_field) 
{	
	$tags = get_post_custom_values($meta_field);
	if(!empty($tags[0]))
	{
		$tags_array = explode($separator, $tags[0]);
	}
	return($tags_array); // return an array of tags
} 


// prepare formatted tag list from $tags_array
function output_bunny_tags($tags_array, $before, $after, $separator) 
{	
	if(!empty($tags_array))
	{
		$tags_list=$before; // HTML to display before the list of tags
		foreach($tags_array as $tag) // go through all tags for the post
		{
			// no spaces in tags version
			// $tag_link='<a href="http://bitacoras.com/canales/' . rawurlencode($tag) . '" title="' . __('en Bitacoras encuentre blogs que hablan acerca de ', 'BunnyTags') . ' \'' . $tag . '\'." rel="tag">' . $tag . '</a>' . $separator; // make a link to the technorati tag page, with tag link text

			// multiple word tags fix provided by ClaytonGray
			$display_tag = urldecode($tag);
			$display_tag = str_replace("+", " ", $display_tag);
			$tag_link='<a href="http://bitacoras.com/canales/' . urlencode(urldecode($tag)) . '" title="' . __('en Bitacoras encuentre blogs que hablan acerca de ', 'BunnyTags') . ' \'' . urldecode($tag) . '\'." rel="tag">' . $display_tag . '</a>' . $separator; // make a link to the technorati tag page, with tag link text

			$tags_list.=$tag_link; // stick it on the end of the growing list
		}
		$chomp = 0 - strlen($separator);
		$tags_list=substr($tags_list, 0, $chomp);// remove the last separator
		$tags_list.=$after; // HTML to display after the list of tags
	}
	return($tags_list);  // return the nice little html-ized list of technorati tags
}

// prepare the formatted list of tags -- this is what you mess with if your custom field values aren't formatted like described in the instructions
function get_bunny_tags_list($before, $after, $separator)
{
	global $bunny_strict;
	
	$technorati_tags=get_bunny_tags(' ', 'tags');
	$label="Tags";
	if(empty($technorati_tags)&&!$bunny_strict)
	{
		$technorati_tags=get_bunny_tags(', ', 'keywords');
		$label="Keywords";
	}
	$technorati_tags_list=output_bunny_tags($technorati_tags, $before, $after, $separator);
	return($technorati_tags_list);
}


// stick the list of tags at the end of the_content if we're in a feed
function append_tags($content)
{
	global $feed;
	if (!empty($feed)&&!is_single()) 
	{
		$tags_list=get_bunny_tags_list('<p class="bitatags"><img src="http://img11.imageshack.us/img11/9247/bitacoras.png" alt="Tags Bitacotas" /> ', '</p>', ', ');
		$content.=$tags_list;
	}
	return $content;	
}

// output textarea to easily add tags in admin menu (addition to the post form)
function add_tags_textinput() {
	global $post;
	
	$tags = get_post_meta($post->ID, 'tags', true);
	
	echo '<fieldset id="posttags" style="margin-bottom: 15px; border: 1px solid #ccc;"><legend>Tags Bitacoras</legend>';
	echo '<div>';
	echo '<input type="hidden" name="bunny-key" id="bunny-key" value="' . wp_create_nonce('bunny') . '" />';
	echo '<input type="text" name="tags" id="tags" size="45" style="width: 95%" value="' . $tags . '" /><br />' . __('<small>Los tags son palabras clave que describen el contenido de tu articulo (post)<br/>Separa los tags con un espacio, cuando uses tags de dos o más palabras utiliza un signo "+" (ejm: "Noticias+Peru"),<br/> evita usar palabras como "para" o "del" que no describen nada, para los tags sólo se tomarán en cuenta palabras que tengan de 3 a 30 caracteres</small>', 'BunnyTags') . '</div></fieldset>';
	
}

// general custom field update function
function bt_update_tags($id)
{

  // authorization
	if ( !current_user_can('edit_post', $id) )
		return $id;
	// origination and intention
	if ( !wp_verify_nonce($_POST['bunny-key'], 'bunny') )
		return $id;
	$setting = $_POST['tags'];
	$meta_exists=update_post_meta($id, 'tags', $setting);
	if(!$meta_exists)
	{
		add_post_meta($id, 'tags', $setting);	
	}
}

// start serious stuff
// localization disabled because it breaks in 1.2
// load_plugin_textdomain('BunnyTags');

// ACTION!

add_action('simple_edit_form', 'add_tags_textinput');
add_action('edit_form_advanced', 'add_tags_textinput');
add_action('edit_page_form', 'add_tags_textinput');
add_action('edit_post', 'bt_update_tags');
add_action('publish_post', 'bt_update_tags');
add_action('save_post', 'bt_update_tags');

add_filter('the_content', 'append_tags');

// TEMPLATE FUNCTION

// use this function to output as tags the space-separated content of the meta field "tags"; depending on your setting of $strict at the beginning of this file, the function may treat as tags the comma-separated content of the meta field "keywords" if "tags" doesn't exist. You may pass arguments $before, $after, and $separator to control the formatting.

function the_bita_tags($before = '<span class="bitatags">', $after = '</span>', $separator = ', ')
{
	print(get_bunny_tags_list($before, $after, $separator));
}

?>

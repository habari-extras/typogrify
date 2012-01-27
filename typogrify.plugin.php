<?php

	/**
	 * Makes text pretty.
	 * 
	 * @todo Refactor the rest of the PHP-Typogrify and SmartyPants code into the Typogrify class so we don't have a ton of global functions.
	 */
	class Typogrify_Plugin extends Plugin {

		public function filter_plugin_config ( $actions, $plugin_id ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
				$actions[] = _t( 'Configure' );
			}
			
			return $actions;
			
		}
		
		public function action_plugin_ui ( $plugin_id, $action ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
				
				if ( $action == _t( 'Configure' ) ) {
					
					$class_name = strtolower( get_class( $this ) );
					
					$form = new FormUI( $class_name );
					
					$form->append( 'fieldset', 'standard_options', 'Standard Options' );
					$form->standard_options->append( 'checkbox', 'do_amp', 'typogrify__do_amp', _t( 'Add <code>&lt;span class=&quot;amp&quot;&gt;</code> to ampersands.') );
					$form->standard_options->append( 'checkbox', 'do_widont', 'typogrify__do_widont', _t( 'Try to prevent <a href="http://en.wikipedia.org/wiki/Widows_and_orphans">widows</a> by adding <code>&amp;nbsp;</code> between the last two words in blocks of text.' ) );
					$form->standard_options->append( 'checkbox', 'do_smartypants', 'typogrify__do_smartypants', _t( 'Apply <a href="http://michelf.com/projects/php-smartypants/">SmartyPants</a> to text.' ) );
					$form->standard_options->append( 'checkbox', 'do_caps', 'typogrify__do_caps', _t( 'Add <code>&lt;span class=&quot;caps&quot;&gt;</code> to consecutive capital letters (acronyms, etc.).' ) );
					$form->standard_options->append( 'checkbox', 'do_initial_quotes', 'typogrify__do_initial_quotes', _t( 'Add <code>&lt;span class=&quot;dquo&quot;&gt;</code> to initial double quotes, and <code>&lt;span class=&quot;quo&quot;&gt;</code> to initial single quotes.' ) );
					
					$form->append( 'fieldset', 'special_options', 'Special Options' );
					$form->special_options->append( 'checkbox', 'do_guillements', 'typogrify__do_guillements', _t( 'Add <code>&lt;span class=&quot;dquo&quot;&gt;</code> to initial <a href="http://en.wikipedia.org/wiki/Guillemet">Guillemets</a> (&laquo; or &raquo;) as well.' ) );
					$form->special_options->append( 'checkbox', 'do_dash', 'typogrify__do_dash', _t( 'Add thin spaces (<code>&amp;thinsp;</code>) to both sides of em and en dashes.' ) );
					
					$form->append( 'fieldset', 'additional_options', 'Additional Options' );
					$form->additional_options->append( 'checkbox', 'do_title_case', 'typogrify__title_case', _t( 'Attempt to properly capitalize post titles based on <a href="http://daringfireball.net/2008/05/title_case">rules</a> by John Gruber.' ) );
					
					$form->append( 'submit', 'save', _t( 'Save' ) );
					
					$form->on_success( array( $this, 'updated_config' ) );
					$form->out();
					
				}
				
			}
			
		}
		
		public function updated_config ( $form ) {
			
			$form->save();
			
		}
		
		public function action_plugin_activation ( $file ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				
				// default options: name => default value
				$options[ 'typogrify__do_amp' ] = 1;
				$options[ 'typogrify__do_widont' ] = 1;
				$options[ 'typogrify__do_smartypants' ] = 1;
				$options[ 'typogrify__do_caps' ] = 1;
				$options[ 'typogrify__do_initial_quotes' ] = 1;
				$options[ 'typogrify__do_guillements' ] = 1;
				$options[ 'typogrify__do_dash' ] = 1;
				$options[ 'typogrify__title_case' ] = 1;
				
				foreach ( $options as $option => $value ) {
					
					if ( Options::get( $option ) == null ) {
						Options::set( $option, $value );
					}
					
				}
				
			}
			
		}
		
		public function filter ( $text ) {
			
			if ( Options::get( 'typogrify__do_amp' ) ) {
				$text = amp( $text );
			}
			
			if ( Options::get( 'typogrify__do_widont' ) ) {
				$text = widont( $text );
			}
			
			if ( Options::get( 'typogrify__do_smartypants' ) ) {
			 	// Standard options plus convert_quot ('w') to
			 	// convert &quot; entities, that Habari might
			 	// already have converted '"' characters into.
				$text = SmartyPants( $text, "qbdew" );
			}
			
			if ( Options::get( 'typogrify__do_caps' ) ) {
				$text = caps( $text );
			}
			
			if ( Options::get( 'typogrify__do_initial_quotes' ) ) {
				$text = initial_quotes( $text );
			}
			
			if ( Options::get( 'typogrify__do_guillemets' ) ) {
				$text = initial_quotes( $text, true );
			}
			
			if ( Options::get( 'typogrify__do_dash' ) ) {
				$text = dash( $text );
			}
			
			return $text;
			
		}
		
		public function filter_post_title_out ( $title ) {
			
			if ( Options::get( 'typogrify__title_case' ) ) {
				$title = Typogrify::title_case( $title );
			}
			
			// for now, just bypass the rest of the filters - they cause problems ATM
			// return $title;
			
			return $this->filter( $title );
			
		}
		
		public function filter_post_content_out ( $content ) {
			
			return $this->filter( $content );
			
		}
		
		public function filter_post_content_excerpt_out ( $excerpt ) {
			
			return $this->filter( $excerpt );
			
		}
		
		public function filter_comment_content_out ( $comment ) {
			
			return $this->filter( $comment );
			
		}
		
		public function filter_comment_name_out ( $name ) {
			
			return $this->filter( $name );
			
		}
		
		public function filter_post_tags_out ( $tags ) {
			
			return $this->filter( $tags );
			
		}
		
		public function filter_post_title_atom ( $title ) {
			
			if ( Options::get( 'typogrify__title_case' ) ) {
				$title = Typogrify::title_case( $title );
			}
			
			// for now, just bypass the rest of the filters - they cause problems ATM
			// return $title;
			
			return $this->filter( $title );
			
		}
		
		public function filter_post_content_atom ( $content ) {
			
			return $this->filter( $content );
			
		}
		
		public function filter_post_content_excerpt_atom ( $excerpt ) {
			
			return $this->filter( $excerpt );
			
		}
		
		public function filter_comment_content_atom ( $comment ) {
			
			return $this->filter( $comment );
			
		}
		
		public function filter_comment_name_atom ( $name ) {
			
			return $this->filter( $name );
			
		}
		
		/**
		 * Set all our filters to run after everything else did
		 */
		public function set_priorities ( ) {
			
			return array(
				'filter_post_title_out' => 10,
				'filter_post_content_out' => 10,
				'filter_post_content_excerpt_out' => 10,
				'filter_comment_content_out' => 10,
				'filter_comment_name_out' => 10,
				'filter_post_tags_out' => 10,
				
				'filter_post_title_atom' => 10,
				'filter_post_content_atom' => 10,
				'filter_post_content_excerpt_atom' => 10,
				'filter_comment_content_atom' => 10,
				'filter_comment_name_atom' => 10,
			);
			
		}
		
		public function action_init ( ) {
			
			include( 'php-typogrify.php' );
			
			// include the typogrify class
			require( 'typogrify.php' );
			
		}
		
	}

?>
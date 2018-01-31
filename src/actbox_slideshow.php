<?php
/**
 * Action box that displays images from a MediaDB folder, e.g. as a slideshow
 *
 * @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 * You can redistribute and/or modify this script under the terms of the GNU General Public
 * License (GPL) version 2, provided that the copyright and license notes, including these
 * lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.
 *
 * @package Papaya-Modules
 * @subpackage Free-Media
 * @version $Id: actbox_thumbnail.php 39733 2014-04-08 18:10:55Z weinert $
 */

/**
 * Action box that displays images from a MediaDB folder, e.g. as a slideshow
 *
 * @package Papaya-Modules
 * @subpackage Free-Media
 */
class actionbox_slideshow extends base_actionbox {

  /**
   * Preview possible
   * @var boolean $preview
   */
  var $preview = TRUE;

  /**
   * MediaDB Object Instance
   * @var base_mediadb $mediaDB
   */
  var $mediaDB = NULL;

  /**
   * Thumbnail object
   * @var base_thumbnail $thumbnail
   */
  var $thumbnail = NULL;

  /**
   * Content edit fields
   * @var array $editFields
   */
  var $editFields = array(
    'directory' => array('Folder', 'isNum', TRUE, 'mediafolder', '', '', ''),
    'limit' => array('Limit', 'isNum', TRUE, 'input', 10, 'Use 0 for unlimited', 0),
    'resize' => array(
      'Resize mode',
      'isAlpha',
      TRUE,
      'combo',
      array(
        'abs' => 'Absolute',
        'max' => 'Maximum',
        'min' => 'Minimum',
        'mincrop' => 'Minimum cropped'
      ),
      '',
      'max'
    ),
    'imagewidth' => array('Image width', 'isNum', TRUE, 'input', 5, '', 100),
    'imageheight' => array('Image height', 'isNum', TRUE, 'input', 5, '', 100),
    'autoplay' => array('Autoplay', 'isNum', TRUE, 'yesno', NULL, '', 1),
    'animation_speed' => array(
      'Animation speed',
      'isNum',
      TRUE,
      'input',
      10,
      'Show each image for this number of seconds',
      1
    )
  );

  /**
  * Get parsed data
  *
  * @return string $result
  */
  public function getParsedData() {
    $this->setDefaultData();
    $result = '';

    $this->mediaDB = &base_mediadb::getInstance();
    $limit = isset($this->data['limit']) && $this->data['limit'] > 0 ? $this->data['limit'] : NULL;
    $files = $this->mediaDB->getFiles($this->data['directory'], $limit);
    if (count($files) > 0) {
      $outputFiles = array();
      foreach ($files as $file) {
        if ($this->isWebImage($file)) {
          $thumbSrc = $this->getWebMediaLink(
            $this->thumbnail()->getThumbnail(
              $file['file_id'],
              NULL,
              $this->data['imagewidth'],
              $this->data['imageheight'],
              $this->data['resize']
            ),
            'thumb',
            empty($file['file_title']) ? '' : papaya_strings::escapeHTMLChars($file['file_title'])
          );
          $outputFiles[] = $thumbSrc;
        }
      }
    }
    if (count($outputFiles) > 0) {
      $result = '<slideshow-box>'.LF;
      $result .= sprintf(
        '<settings total="%d" autoplay="%d" />'.LF,
        count($outputFiles),
        $this->data['autoplay']
      );
      foreach ($outputFiles as $file) {
        $result .= sprintf(
          '<slide src="%s" />'.LF,
          $file
        );
      }
      $result .= '</slideshow-box>'.LF;
    }
    return $result;
  }

  /**
   * Get/set/initialize the thumbnail object
   *
   * @param base_thumbnail $thumbnail optional, default value NULL
   * @return base_thumbnail
   */
  public function thumbnail($thumbnail = NULL) {
    if (isset($thumbnail)) {
      $this->thumbnail = $thumbnail;
    } elseif (!isset($this->thumbnail)) {
      $this->thumbnail = new base_thumbnail();
    }
    return $this->thumbnail;
  }

  /**
  * Is web image?
  * @param array $image
  * @return boolean
  */
  private function isWebImage($image) {
    $types = array(
      'image/jpeg',
      'image/gif',
      'image/png'
    );
    if (isset($image['mimetype']) && in_array($image['mimetype'], $types)) {
      return TRUE;
    }
    return FALSE;
  }
}
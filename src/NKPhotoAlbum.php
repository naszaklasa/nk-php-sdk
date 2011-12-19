<?php

/*
 * Copyright 2011 Nasza Klasa Spółka z ograniczoną odpowiedzialnością
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @package Service
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKPhotoAlbum extends NKObject
{
  private $id;
  private $title;
  private $ownerId;
  private $description;
  private $mediaMimeType;
  private $thumbnailUrl;
  private $mediaItemCount;

  public function __construct($id = null, $owner_id = null)
  {
    $this->id = $id;
    $this->ownerId = $owner_id;
  }

  public function id($id = null)
  {
    return $this->id;
  }

  public function title()
  {
    return $this->title;
  }

  public function ownerId()
  {
    return $this->ownerId;
  }

  public function description()
  {
    return $this->description;
  }

  public function mediaMimeType()
  {
    return $this->mediaMimeType;
  }

  public function thumbnailUrl()
  {
    return $this->thumbnailUrl;
  }

  public function mediaItemCount()
  {
    return $this->mediaItemCount;
  }

  /**
   * 
   * @access private
   * @param array $data
   * @return void
   */
  public function assignData(array $data)
  {
    $this->id = $data['id'];
    $this->title = $data['title'];
    $this->ownerId = $data['ownerId'];
    $this->description = $data['description'];
    $this->mediaMimeType = $data['mediaMimeType'][0];
    if (isset($data['thumbnailUrl'])) {
      $this->thumbnailUrl = $data['thumbnailUrl'];
    }
    $this->mediaItemCount = $data['mediaItemCount'];
  }
}

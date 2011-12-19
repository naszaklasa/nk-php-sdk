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
class NKPhoto extends NKObject
{
  private $id;
  private $albumId;
  private $created;
  private $description;
  private $mimeType;
  private $thumbnailUrl;
  private $url;
  private $nk_addedBy;

  public function albumId()
  {
    return $this->albumId;
  }

  public function created()
  {
    return $this->created;
  }

  public function description()
  {
    return $this->description;
  }

  public function id()
  {
    return $this->id;
  }

  public function mimeType()
  {
    return $this->mimeType;
  }

  public function ownerId()
  {
    return $this->nk_addedBy;
  }

  public function nk_addedBy()
  {
    return $this->nk_addedBy;
  }

  public function thumbnailUrl()
  {
    return $this->thumbnailUrl;
  }

  public function url()
  {
    return $this->url;
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
    $this->nk_addedBy = $data['nk_addedBy'];
    $this->created = $data['created'];
    if (isset($data['description'])) {
      $this->description = $data['description'];
    }
    $this->thumbnailUrl = $data['thumbnailUrl'];
    $this->mimeType = $data['mimeType'];
    $this->albumId = $data['albumId'];
    $this->url = $data['url'];
  }
}

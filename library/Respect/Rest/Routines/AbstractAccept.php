<?php

namespace Respect\Rest\Routines;

use SplObjectStorage;
use Respect\Rest\Request;

abstract class AbstractAccept extends AbstractRoutine implements ProxyableWhen, ProxyableThrough
{

    protected $acceptMap = array();
    protected $negotiated = null;

    public function __construct(array $acceptMap = array())
    {
        if (!array_filter($acceptMap, 'is_callable'))
            throw new \Exception(''); //TODO

            $this->negotiated = new SplObjectStorage;
        $this->acceptMap = $acceptMap;
    }

    protected function negotiate(Request $request)
    {
        if (!$request->hasVar('SERVER', static::ACCEPT_HEADER))
            return false;

        $acceptHeader = $request->getVar('SERVER', static::ACCEPT_HEADER);
        $acceptParts = explode(',', $acceptHeader);
        $acceptList = array();
        foreach ($acceptParts as $k => &$acceptPart) {
            $parts = explode(';q=', trim($acceptPart));
            $provided = array_shift($parts);
            $quality = array_shift($parts) ? : (10000 - $k) / 10000;
            $acceptList[$provided] = $quality;
        }
        arsort($acceptList);
        foreach ($acceptList as $requested => $quality)
            foreach ($this->acceptMap as $provided => $callback)
                if ($this->compareItens($requested, $provided))
                    return $this->negotiated[$request] = $callback;

        return false;
    }

    public function through(Request $request, $params)
    {
        if (!isset($this->negotiated[$request])
            || false === $this->negotiated[$request])
            return;
        
        return $this->negotiated[$request];
    }

    public function when(Request $request, $params)
    {
        return false !== $this->negotiate($request);
    }

    protected function compareItens($requested, $provided)
    {
        return $requested == $provided;
    }

}

/**
 * LICENSE
 *
 * Copyright (c) 2009-2011, Alexandre Gomes Gaigalas.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 *
 *     * Neither the name of Alexandre Gomes Gaigalas nor the names of its
 *       contributors may be used to endorse or promote products derived from this
 *       software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */
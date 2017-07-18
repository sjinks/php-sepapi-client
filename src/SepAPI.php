<?php

namespace WildWolf;

class SepAPI
{
    /**
     * @var \WildWolf\CurlWrapperInterface
     */
    private $curl;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $token;

    /**
     * @param string $endpoint
     * @param string $token
     * @throws \InvalidArgumentException
     */
    public function __construct(string $endpoint, string $token = null)
    {
        if (!$endpoint) {
            throw new \InvalidArgumentException();
        }

        $this->endpoint = $endpoint;
        $this->token    = $token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }

    private function maybeInstantiateCurl()
    {
        if (!$this->curl) {
            $this->curl = new \WildWolf\CurlWrapper();
        }
    }

    /**
     * @param string $url
     * @param array $post
     * @throws \Exception
     * @return mixed
     */
    private function doRequest(string $url, array $post = null)
    {
        $this->maybeInstantiateCurl();

        $this->curl->reset();
        $this->curl->setOptions([
            CURLOPT_URL            => $this->endpoint . $url,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $this->token],
            CURLOPT_POST           => $post !== null,
            CURLOPT_POSTFIELDS     => $post,
        ]);

        $response = $this->curl->execute();
        $code     = (int)$this->curl->info(CURLINFO_HTTP_CODE);

        if ($code !== 200) {
            throw new \Exception($response, $code);
        }

        return json_decode($response);
    }

    /**
     * @param string $phone
     * @return mixed
     */
    public function validatePhone(string $phone)
    {
        return $this->doRequest('/users/validate/phone/' . $phone);
    }

    /**
     * @param string $id
     * @param string $token
     * @param string $phone
     * @return mixed
     */
    public function smsLogin(string $id, string $token, string $phone)
    {
        return $this->doRequest(
            '/users/smslogin',
            ['fbid' => $id, 'token' => $token, 'phone' => $phone]
        );
    }

    /**
     * @param int $uid
     * @return mixed
     */
    public function trackUpload($uid)
    {
        return $this->doRequest('/users/trackupload', ['id' => $uid]);
    }
}

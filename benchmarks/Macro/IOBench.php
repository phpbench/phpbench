<?php

namespace PhpBench\Benchmarks\Macro;

/**
 * @AfterMethods({"tearDown"})
 */
class IOBench
{
    /**
     * @var array
     */
    private $array;

    /**
     * @var resource
     */
    private $resource;

    public function setUp()
    {
        $this->array = [
            'one' => 'one',
        ];
    }

    public function tearDown()
    {
        fclose($this->resource);
    }

    public function benchArrayAccess()
    {
        $this->array['one'];
    }

    public function benchTcpReadWrite()
    {
        $this->resource = stream_socket_client('tcp://localhost:8080', $errNo, $errString, 10);
        fwrite($this->resource, "GET / HTTP/1.0\r\n\r\n");
        stream_get_contents($this->resource);
    }

    public function benchTcpConnect()
    {
        $this->resource = stream_socket_client('tcp://localhost:8080', $errNo, $errString, 10);
    }

    /**
     * @BeforeMethods({"benchTcpConnect"})
     */
    public function benchTcpWrite()
    {
        fwrite($this->resource, "GET / HTTP/1.0\r\n\r\n");
    }

    /**
     * @BeforeMethods({"benchTcpConnect", "benchTcpWrite"})
     */
    public function benchTcpRead()
    {
        stream_get_contents($this->resource);
    }

    public function benchHttpGetLocal()
    {
        file_get_contents('http://localhost:8080');
    }

    public function benchHttpGetNetwork()
    {
        file_get_contents('http://192.168.1.28:8081');
    }

    public function benchHttpGetGoogle()
    {
        file_get_contents('http://www.dantleech.com/sleep.php');
    }
}

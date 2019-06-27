<?php

namespace invoice\src;

class InvoiceClient
{
    const KJFP = 'ECXML.FPKJ.BC.E_INV';
    const DOWNLOAD = 'ECXML.FPXZ.CX.E_INV';
    const EMAIL = 'ECXML.EMAILPHONEFPTS.TS.E.INV';
    const HOST = 'http://fw1.shdzfp.com:9000/axis2/services/SajtIssueInvoiceService?wsdl';

    /**
     * create invoice
     * @param  array  $params    [description]
     * @param  array  $config [description]
     * @return [type]         [description]
     */
    public function create(array $params, array $config = [])
    {
        $data = [];
        if ($params['invoice_type'] == 2) {
            $data['ghfmc'] = $params['invoice_title'];
            $data['ghfqylx'] = '01';
        } else {
            $data['ghfmc'] = '个人';
            $data['ghfqylx'] = '03';
        }
        $items = [];
        //查询子项目
        foreach ($params['items'] as $key => $item) {
            $show_name = $item['name'];
            $items[$key]['XMMC'] = $show_name;
            $items[$key]['XMSL'] = sprintf('%.8f', $item['quantity']);
            $items[$key]['XMDJ'] = sprintf('%.8f', $item['price']);
            $items[$key]['SPBM'] = $item['spbm'];
            $items[$key]['ZXBM'] = $item['zxbm'];
            $items[$key]['XMJE'] = sprintf('%.2f', $item['price'] * $item['quantity']);


            if ($params['discount'] && $params['discount'] != 0.00 && $key == 0) {
                $items[$key]['FPHXZ'] = 2;
                $items[$key]['discount'] = [
                    'XMMC' => $show_name,
                    'XMSL' => '-' . sprintf('%.8f', 1),
                    'FPHXZ' => '1',
                    'XMDJ' => sprintf('%.8f', $params['discount']),
                    'SPBM' => $item['spbm'],
                    'ZXBM' => $item['id'],
                    'XMJE' => '-' . sprintf('%.2f', $params['discount'])
                ];
            } else {
                $items[$key]['FPHXZ'] = 0;
            }
            if ($key == 0) {
                $data['kpxm'] = $show_name;
            }
        }
        $data['items'] = $items;
        $data['mobile'] = isset($params['mobile']) ? $params['mobile'] : '';
        $data['kplx'] = '1';
        $data['czdm'] = '10';
        $data['kphjje'] = sprintf('%.2f', $params['sum']);
        $data['hjbhsje'] = sprintf('%.2f', $params['sum']);
        $data['hjse'] = '';
        $data['ddh'] = $params['trade_no'];
        $content = InvoiceCore::getInstance($config)->getContent($data);
        $xml = InvoiceCore::getInstance($config)->getXml(self::KJFP,$content);
        $client = new \SoapClient(self::HOST, array(
            'trace' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ));
        $functionName = 'eiInterface';
        $response = $client->__soapCall($functionName, array(
            $functionName => array('in0' => $xml),
        ));
        $responseContent = $response->return;
        $content = simplexml_load_string($responseContent);
        return $content;
    }

    /**
     * download invoice
     * @param  array  $params    [description]
     * @param  array  $config [description]
     * @return [type]         [description]
     */
    public function download(array $params, array $config = [])
    {
        $len = strlen($params['trade_no']);
        $data['lsh'] = str_repeat('0', 20 - $len) . $params['trade_no'];
        $data['pdf_xzfs'] = 1;
        $data['ddh'] = $params['trade_no'];
        $content = InvoiceCore::getInstance($config)->getDownload($data);
        $xml = InvoiceCore::getInstance($config)->getXml(self::DOWNLOAD,$content);

        $client = new \SoapClient(self::HOST, array(
            'trace' => true,
            'cache_wsdl' => WSDL_CACHE_BOTH,
        ));
        $functionName = 'eiInterface';
        $response = $client->__soapCall($functionName, array(
            $functionName => array('in0' => $xml),
        ));
        $responseContent = $response->return;
        $return = simplexml_load_string($responseContent);
        if ($return->returnStateInfo->returnCode[0] == '0000') {
            //PDF_XZFS 1 是pdf内容 必然要解压
            if ($return->Data->dataDescription->zipCode[0] == 1) {
                $content = gzdecode(base64_decode($return->Data->content[0]));
                $pdf = simplexml_load_string($content);
                return $pdf;
            }
        } else {
            //状态有误
            echo "\n INVOICE INFO ERROR DOWNLOAD  \t {$return->returnStateInfo->returnCode[0]}\t";
        }
    }

    /**
     * email invoice
     * @param  array  $params    [description]
     * @param  array  $config [description]
     * @return [type]         [description]
     */
    public function email(array $params, array $config = [])
    {
        $len = strlen($params['trade_no']);
        $data['lsh'] = str_repeat('0', 20 - $len) . $params['trade_no'];
        $data['eamil'] = $params['email'];
        $data['fp_dm'] = $params['fp_dm'];
        $data['fp_hm'] = $params['fp_hm'];
        $content = InvoiceCore::getInstance($config)->getEmail($data);
        $xml = InvoiceCore::getInstance($config)->getXml(self::EMAIL,$content);

        $client = new \SoapClient(self::HOST, array(
            'trace' => true,
            'cache_wsdl' => WSDL_CACHE_BOTH,
        ));
        $functionName = 'eiInterface';
        $response = $client->__soapCall($functionName, array(
            $functionName => array('in0' => $xml),
        ));
        $responseContent = $response->return;
        $return = simplexml_load_string($responseContent);
        if ($return->returnStateInfo->returnCode[0] == '0000') {
            //修改状态
            return $return;
        } else {
            echo "\n INVOICE INFO ERROR EMAIL \t {$return->returnStateInfo->returnCode[0]}\t";
        }
    }

}
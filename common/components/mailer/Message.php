<?php

namespace common\components\mailer;

use yii\mail\BaseMessage;

/**
 * Description of Message
 *
 * @author elbabuino
 */
class Message extends BaseMessage
{
    private $order_id;
    /**
     * @var string Text content
     */
    private $messageText;

    /**
     * @var string Html content
     */
    private $messageHtml = null;

    /**
     * @var string Message charset
     */
    private $charset;

    /**
     * @var string Message sender
     */
    private $from;

    /**
     * @var string replyTo
     */
    private $replyTo;

    /**
     * @var string To
     */
    private $to;

    /**
     * @var string CC
     */
    private $cc;

    /**
     * @var string BCC
     */
    private $bcc;

    /**
     * @var string Subject
     */
    private $subject;

    /**
     * @var integer Sending time for debugging
     */
    private $time;

    /**
     * In Yii2 dev panel some bug and this method have to return information about result of sending
     * @return \yashop\ses\Message Message class instance.
     */
    public function getSwiftMessage()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @inheritdoc
     */
    public function setCharset($charset)
    {
        //lambda always set charset to utf-8
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @inheritdoc
     */
    public function setFrom($from, $name = null)
    {
        if (!isset($name)) {
            $name = gethostname();
        }
        if (!is_array($from) && isset($name)) {
            $from = array($from => $name);
        }
        list($address) = array_keys($from);
        $name = $from[$address];
        $this->from = '"'.$name.'" <'.$address.'>';

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @inheritdoc
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @inheritdoc
     */
    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @inheritdoc
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTextBody($text)
    {
        $this->messageText = $text;
        $this->setBody($this->messageText, $this->messageHtml);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHtmlBody($html)
    {
        $this->messageHtml = $html;
        $this->setBody($this->messageText, $this->messageHtml);

        return $this;
    }
    
    public function getHtmlBody()
    {
        return $this->messageHtml;
    }

    /**
     * @inheritdoc
     */
    public function getBody()
    {
        return $this->messageText;
    }

    /**
     * @inheritdoc
     */
    public function setBody($text, $html = null)
    {
        $this->messageText = $text;
        $this->messageHtml = $html ?? $text;
    }

    /**
     * @inheritdoc
     */
    public function attach($fileName, array $options = [])
    {
        $name = $fileName;
        $mimeType = 'application/octet-stream';

        if (!empty($options['fileName'])) {
            $name = $options['fileName'];
        }
        if (!empty($options['contentType'])) {
            $mimeType = $options['contentType'];
        }

        //does nothing, to be updated with s3 upload later
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attachContent($content, array $options = [])
    {
        $name = 'file 1';
        $mimeType = 'application/octet-stream';

        if (!empty($options['fileName'])) {
            $name = $options['fileName'];
        }
        if (!empty($options['contentType'])) {
            $mimeType = $options['contentType'];
        }

        //does nothing, to be updated later
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function embed($fileName, array $options = [])
    {
        //does nothing, to be updated with s3 upload later
        return $this->attach($fileName, $options);
    }

    /**
     * @inheritdoc
     */
    public function embedContent($content, array $options = [])
    {
        //does nothing, to be updated later
        return $this->attachContent($content, $options);
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return $this->messageText;
    }

    public function setDate($time)
    {
        $this->time = $time;

        return $this;
    }

    public function getDate()
    {
        return $this->time;
    }

    public function getHeaders()
    {
        //todo: make headers for debug
        return '';
    }

    public function setHeader($key, $value)
    {
        //headers are set in lambda
        return $this;
    }
    
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        
        return $this;
    }
    
    public function getOrderId()
    {
        return $this->order_id;
    }
}
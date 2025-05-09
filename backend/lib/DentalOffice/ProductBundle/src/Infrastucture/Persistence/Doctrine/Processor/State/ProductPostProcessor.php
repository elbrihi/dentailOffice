<?php

namespace Stock\ProductBundle\Infrastucture\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Stock\ProductBundle\Domain\Entity\Category;
use Stock\ProductBundle\Domain\Entity\Product;
use Stock\SupplierBundle\Domain\Entity\Supplier;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProductPostProcessor implements ProcessorInterface
{
    // providers
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private Security $security, 
        private EntityManagerInterface $entityManager ,
        private ClockInterface $clock,
        
    )
    {
        
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Product
    {
     
        
        $product = new Product();
        /** @var Request $request */
        $request = $context['request'];
        
        //$this->clock->now();

        $categoryId = $uriVariables["categoryId"];
        $categories= $this->entityManager->getRepository(Category::class)->findOneBy([
            'id' => $categoryId,
        ]);
   
        $supplierId =  $uriVariables["supplierId"];

        $supplier = $this->entityManager->getRepository(Supplier::class)->findOneBy([
                'id' => $supplierId
        ]);
       
        
        
        $product->setProductName($data->getProductName());
        $product->setProductDescription($data->getProductDescription());
        $product->setProductTax($data->getProductTax());
        $product->setUnitPrice($data->getUnitPrice());
        $product->setCategory($categories);
        $product->setSupplier($supplier);
        $product->setCategory($categories);
        $product->setCreatedAt($this->clock->now());
        $product->setModifiedAt($this->clock->now());
        $product->setUser($this->security->getUser());
        $product->status = true;
        return $this->persistProcessor->process($product, $operation, $uriVariables, $context);
    
    }
}

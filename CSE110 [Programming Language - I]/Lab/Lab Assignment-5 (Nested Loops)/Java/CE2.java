import java.util.Scanner;

public class CE2 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        while(true)
        {
            System.out.print("Enter Number: ");
            int num = sc.nextInt();
            if (num % 2 != 0)
            {
                break;
            }
            int divCount = 0;
            for(int i = 1; i <= num; i++)
            {
                if(num % i == 0)
                {
                    divCount++;
                }
            }
            System.out.println(num + " has " + divCount + " divisors");
        }
    }
}
